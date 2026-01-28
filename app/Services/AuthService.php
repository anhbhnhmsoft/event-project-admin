<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\Config;
use App\Models\Event;
use App\Models\EventUserHistory;
use App\Models\User;
use App\Utils\Constants\ConfigName;
use App\Utils\Constants\EventStatus;
use App\Utils\Constants\EventUserHistoryStatus;
use App\Utils\Constants\Language;
use App\Utils\Constants\RoleUser;
use App\Utils\Constants\StoragePath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\App;
use App\Models\UserResetCode;
use App\Mail\ResetPasswordMail;
use App\Mail\VerifyEmailMail;
use App\Models\EventSeat;
use App\Models\Organizer;
use App\Utils\Constants\CommonStatus;
use App\Utils\Constants\ConfigType;
use App\Utils\Constants\EventSeatStatus;
use App\Utils\Helper;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AuthService
{
    public function getSupportLink()
    {
        return Config::query()->whereIn('config_key', [
            ConfigName::LINK_FACEBOOK_SUPPORT->value,
            ConfigName::LINK_ZALO_SUPPORT->value
        ])->pluck('config_value', 'config_key');
    }
    public function login(array $data): array
    {
        try {
            $username = $data['username'];
            $type = $this->detectUsernameType($username);

            $query = User::query()->where('organizer_id', (int) $data['organizer_id']);

            if ($type === 'email') {
                $query->where('email', $username);
            } elseif ($type === 'phone') {
                $query->where('phone', $username);
            } else {
                throw new ServiceException(__('auth.error.invalid_credentials'));
            }

            $user = $query->first();

            if (!$user) {
                // Return same error to prevent username enumeration
                throw new ServiceException(__('auth.error.invalid_credentials'));
            }
            if ($user->inactive) {
                throw new ServiceException(__('auth.error.account_inactivated'));
            }
            if (! Hash::check($data['password'], $user->password)) {
                throw new ServiceException(__('auth.error.invalid_credentials'));
            }

            // Only require email verification for email users
            if ($type === 'email' && !$user->hasVerifiedEmail()) {
                return [
                    'status' => false,
                    'message' => __('auth.error.unverified_email'),
                    'unverified_email' => true
                ];
            }

            // Require phone verification for phone user
            if ($type === 'phone' && $user->phone_verified_at == null) {
                $this->sendAuthenticationCode($user->phone, 'login', $user->organizer_id);
                return [
                    'status' => false,
                    'message' => __('auth.error.unverified_phone'),
                    'unverified_phone' => true
                ];
            }

            $user->lang = $data['locate'] ?? Language::VI->value;
            $user->save();

            $token = $user->createToken('api', expiresAt: now()->addDays(30))->plainTextToken;
            return [
                'status' => true,
                'token' => $token,
                'user' => $user,
            ];
        } catch (ServiceException $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            Log::error('AuthService login error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function register(array $data): array
    {
        DB::beginTransaction();
        try {
            $username = $data['username'];
            $type = $this->detectUsernameType($username);
            $organizerId = (int) $data['organizer_id'];

            $userData = [
                'name' => trim($data['name']),
                'password' => Hash::make($data['password']),
                'organizer_id' => $organizerId,
                'role' => RoleUser::CUSTOMER->value,
                'lang' => request()->input('locate') ?? Language::VI->value,
            ];

            // Handle Phone Registration
            if ($type === 'phone') {
                $userData['phone'] = $username;
                // Phone check unique
                if (User::where('phone', $username)->where('organizer_id', $organizerId)->exists()) {
                    throw new ServiceException(__('auth.error.phone_already_registered'));
                }
            }
            // Handle Email Registration
            elseif ($type === 'email') {
                $userData['email'] = $username;
                if (User::where('email', $username)->where('organizer_id', $organizerId)->exists()) {
                    throw new ServiceException(__('auth.error.email_already_registered'));
                }
            } else {
                throw new ServiceException(__('auth.error.invalid_username'));
            }

            $user = User::query()->create($userData);

            // Send email verification if email type
            if ($type === 'email') {
                $url = URL::temporarySignedRoute(
                    'verification.verify',
                    now()->addMinutes(60),
                    ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]
                );
                Mail::to($user->email)->queue(new VerifyEmailMail($url, $userData['lang']));
            } elseif ($type === 'phone') {
                // Send OTP for phone
                $this->sendAuthenticationCode($username, 'register', $organizerId);
            }

            DB::commit();

            return [
                'status' => true,
                'message' => $type === 'phone'
                    ? __('auth.success.register_success_verify_otp')
                    : __('auth.success.register_success_verify_email'),
                'need_verify' => true,
                'username' => $username,
            ];
        } catch (ServiceException $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Register failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function editInfoUser(array $data)
    {
        $user = Auth::user();
        /** @var User $user */
        try {
            $user->name = $data['name'];
            $user->address = $data['address'] ?? null;
            $user->phone = $data['phone'] ?? null;
            $user->introduce = $data['introduce'] ?? null;
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();
            return [
                'status' => true,
                'data' => $user
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function editInfoAvatar($file): array
    {
        $user = Auth::user();
        /** @var User $user */
        try {
            if (!$file instanceof UploadedFile) {
                throw new ServiceException(__('auth.validation.avatar_invalid'));
            }
            $avatarPathNew = $file->store(StoragePath::makePathById(
                type: StoragePath::AVATAR_USER_PATH,
                id: $user->id
            ), 'public');
            if (!$avatarPathNew) {
                throw new ServiceException(__('common.common_error.server_error'));
            }
            if ($user->avatar_path) {
                Storage::delete($user->avatar_path);
            }
            $user->avatar_path = $avatarPathNew;
            $user->save();
            return [
                'status' => true,
                'data' => $user
            ];
        } catch (ServiceException $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function deleteAvatar(): array
    {
        $user = Auth::user();
        /** @var User $user */
        try {
            if ($user->avatar_path) {
                Storage::delete($user->avatar_path);
                $user->avatar_path = null;
                $user->save();
            }
            return [
                'status' => true,
                'data' => $user
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function forgotPasswordForMail(array $data, string $locale = 'vi'): array
    {
        try {
            $query = User::where('email', $data['email']);
            if (isset($data['organizer_id'])) {
                $query->where('organizer_id', $data['organizer_id']);
            }
            $user = $query->first();

            if (!$user) {
                return [
                    'status' => false,
                    'message' => __('auth.error.email_not_found'),
                ];
            }

            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            UserResetCode::where('user_id', $user->id)
                ->where('email', $data['email'])
                ->whereNull('deleted_at')
                ->delete();

            UserResetCode::create([
                'user_id' => $user->id,
                'email' => $data['email'],
                'code' => $code,
                'expires_at' => now()->addMinutes(10),
            ]);

            App::setLocale($locale);

            Mail::to($user->email)->send(new ResetPasswordMail($code, $locale));

            return [
                'status' => true,
                'message' => __('auth.success.reset_sent'),
            ];
        } catch (ServiceException $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function verfifyBackup(array $data): array
    {
        try {
            $user = User::where('email', $data['email'])->first();

            App::setLocale($data['locate']);
            $url = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]
            );

            Mail::to($user->email)->queue(new VerifyEmailMail($url, $data['locate']));

            return [
                'status' => true,
                'message' => __('auth.success.reset_sent'),
            ];
        } catch (ServiceException $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function confirmPassword(array $data): array
    {
        try {
            $username = $data['username'];
            $organizerId = (int) $data['organizer_id'];
            $type = $this->detectUsernameType($username);

            // Find User
            $user = User::where('organizer_id', $organizerId)
                ->where(function ($q) use ($username, $type) {
                    if ($type === 'email') $q->where('email', $username);
                    else $q->where('phone', $username);
                })->first();

            if (!$user) {
                return [
                    'status' => false,
                    'message' => __('auth.error.user_not_found'),
                ];
            }

            if ($type === 'phone') {
                // Verify OTP
                $cacheKey = "otp:forgot_password:{$username}:{$organizerId}";
                $tokenData = Cache::get($cacheKey);

                if (!$tokenData || $tokenData['otp'] !== $data['code']) {
                    return [
                        'status' => false,
                        'message' => __('auth.error.invalid_code'),
                    ];
                }
                Cache::forget($cacheKey); // Clear OTP

            } elseif ($type === 'email') {
                // Verify Reset Code
                $resetCode = UserResetCode::where('user_id', $user->id)
                    ->where('email', $username)
                    ->where('code', $data['code'])
                    ->where('expires_at', '>', now())
                    ->whereNull('deleted_at')
                    ->first();

                if (!$resetCode) {
                    return [
                        'status' => false,
                        'message' => __('auth.error.invalid_code'),
                    ];
                }
                $resetCode->delete();
            } else {
                return [
                    'status' => false,
                    'message' => __('auth.error.invalid_username'),
                ];
            }

            $user->password = Hash::make($data['password']);
            $user->save();

            return [
                'status' => true,
                'message' => __('auth.success.password_changed'),
            ];
        } catch (ServiceException $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        } catch (\Throwable $e) {
            Log::error('confirmPassword error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function checkExpiresAtUser(): array
    {
        try {
            $count = UserResetCode::where('expires_at', '<', now())
                ->forceDelete();

            return [
                'status' => true,
                'message' => __('common.common_success.update_success'),
            ];
        } catch (ServiceException $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function setLanguageUser(User $user, string $locale = 'vi'): array
    {
        try {
            if (!in_array($locale, [Language::VI->value, Language::EN->value])) {
                $locale = Language::VI->value;
            }
            $user->lang = $locale;
            $user->save();
            return [
                'status' => true,
                'message' => __('auth.success.set_lang_success'),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function registerOrganizer(array $data): array
    {
        DB::beginTransaction();

        try {
            $organizer = Organizer::create([
                'name' => $data['name'],
                'status' => CommonStatus::ACTIVE,
            ]);

            $user = \App\Models\User::create([
                'name' => empty($data['user_name']) ? $data['name'] : $data['user_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => RoleUser::ADMIN,
                'phone' => $data['phone'] ?? null,
                'organizer_id' => $organizer->id,
                'lang' => Language::VI,
            ]);
            $configs = [
                [
                    'config_key' => ConfigName::CLIENT_ID_APP->value,
                    'config_type' => ConfigType::STRING->value,
                    'config_value' => '',
                    'organizer_id' => $organizer->id
                ],
                [
                    'config_key' => ConfigName::API_KEY->value,
                    'config_type' => ConfigType::STRING->value,
                    'config_value' => '',
                    'organizer_id' => $organizer->id
                ],
                [
                    'config_key' => ConfigName::CHECKSUM_KEY->value,
                    'config_type' => ConfigType::STRING->value,
                    'config_value' => '',
                    'organizer_id' => $organizer->id
                ],
                [
                    'config_key' => ConfigName::LINK_ZALO_SUPPORT->value,
                    'config_type' => ConfigType::STRING->value,
                    'config_value' => 'https://zalo.me/your-support-link',
                    'organizer_id' => $organizer->id
                ],
                [
                    'config_key' => ConfigName::LINK_FACEBOOK_SUPPORT->value,
                    'config_type' => ConfigType::STRING->value,
                    'config_value' => 'https://facebook.com/your-support-page',
                    'organizer_id' => $organizer->id
                ],
            ];

            Config::query()->insert($configs);

            DB::commit();

            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                Log::error('Failed to send verification email: ' . $e->getMessage());
            }

            return [
                'status' => true,
                'user' => $user,
                'organizer' => $organizer,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Register organizer failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function quickCheckin(array $data): array
    {
        DB::beginTransaction();
        try {
            // Tìm user theo email hoặc phone và organizer
            $user = User::query()
                ->where('organizer_id', $data['organizer_id'])
                ->where(function ($query) use ($data) {
                    if (!empty($data['email'])) {
                        $query->where('email', $data['email']);
                    }
                    if (!empty($data['phone'])) {
                        if (!empty($data['email'])) {
                            $query->orWhere('phone', $data['phone']);
                        } else {
                            $query->where('phone', $data['phone']);
                        }
                    }
                })
                ->first();

            if (!$user) {
                return [
                    'status' => false,
                    'title' => __('event.messages.account_not_found_title'),
                    'message' => __('event.messages.account_not_found_message'),
                ];
            }

            // Tìm vé của user cho sự kiện này
            $history = EventUserHistory::query()
                ->where('user_id', $user->id)
                ->where('event_id', $data['event_id'])
                ->first();

            if (!$history) {
                return [
                    'status' => false,
                    'title' => __('event.messages.ticket_not_found_title'),
                    'message' => __('event.messages.ticket_not_found_message'),
                ];
            }

            // Kiểm tra trạng thái sự kiện
            $event = Event::find($data['event_id']);
            if ($event->status !== EventStatus::ACTIVE->value) {
                return [
                    'status' => false,
                    'title' => __('event.messages.event_not_started_title'),
                    'message' => __('event.messages.event_not_started_message'),
                ];
            }

            // Cập nhật trạng thái check-in
            $history->update([
                'status' => EventUserHistoryStatus::PARTICIPATED->value,
            ]);

            $history->load('seat');

            Log::info('Quick check-in success', [
                'user_id' => $user->id,
                'event_id' => $data['event_id'],
                'ticket_code' => $history->seat?->seat_code,
            ]);


            DB::commit();

            return [
                'status' => true,
                'title' => __('event.messages.checkin_success_title'),
                'message' => __('event.messages.checkin_success_message'),
                'data' => [
                    'ticket_code' => $history->ticket_code,
                    'seat_name' => $history->seat?->seat_code,
                ]
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Quick check-in failed: " . $e->getMessage());

            return [
                'status' => false,
                'title' => __('event.messages.checkin_failed_title'),
                'message' => __('event.messages.checkin_failed_message'),
            ];
        }
    }

    public function quickRegister(array $data): array
    {
        DB::beginTransaction();

        try {
            $userExists = true;
            $seatWasUpdated = false;

            $user = User::query()
                ->where('email', $data['email'])
                ->where('organizer_id', $data['organizer_id'])
                ->first();

            if (! $user) {
                // TẠO MỚI USER
                $user = User::query()->create([
                    'name'              => trim($data['name']),
                    'email'             => $data['email'],
                    'password'          => Hash::make($data['phone']),
                    'organizer_id'      => (int) $data['organizer_id'],
                    'role'              => RoleUser::CUSTOMER->value,
                    'lang'              => $data['lang'] ?? Language::VI->value,
                    'phone'             => $data['phone'],
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                ]);
                $userExists = false; // Đánh dấu là user mới được tạo
            } elseif (! $user->phone) {
                // USER TỒN TẠI, CẬP NHẬT PHONE
                $user->update(['phone' => $data['phone']]);
            }

            // Lấy event
            $event = Event::query()->findOrFail($data['event_id']);

            // Lấy vé cũ (history)
            $history = EventUserHistory::query()
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();

            // Xác định trạng thái vé
            $status = $event->status === EventStatus::ACTIVE->value
                ? EventUserHistoryStatus::PARTICIPATED->value
                : EventUserHistoryStatus::BOOKED->value;

            if (! $history) {

                // Tìm ghế trống
                $seat = EventSeat::query()
                    ->whereHas('area', function (Builder $q) use ($event) {
                        $q->where('event_id', $event->id)->where('vip', false);
                    })
                    ->where('status', EventSeatStatus::AVAILABLE->value)
                    ->whereNull('user_id')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->first();

                if (! $seat) {
                    DB::rollBack();
                    return [
                        'status'  => false,
                        'message' => __('event.validation.no_available_seat'),
                        'title'   => __('event.validation.register_fail_title'),
                    ];
                }

                // Tạo ticket code mới
                do {
                    $ticketCode = 'TICKET-' . Helper::getTimestampAsId();
                } while (EventUserHistory::where('ticket_code', $ticketCode)->exists());

                // Tạo vé mới và GÁN GHẾ MỚI
                EventUserHistory::query()->create([
                    'event_id'      => $event->id,
                    'user_id'       => $user->id,
                    'status'        => $status,
                    'event_seat_id' => $seat->id, // Gán ghế mới
                    'ticket_code'   => $ticketCode,
                ]);

                $seatWasUpdated = true;

                // Cập nhật ghế
                $seat->update([
                    'user_id' => $user->id,
                    'status'  => EventSeatStatus::BOOKED->value,
                ]);
            } else {
                $history->update(['status' => $status]);
            }

            DB::commit();

            $finalHistory = EventUserHistory::query()
                ->with('seat')
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();

            $ticketCode = $finalHistory->ticket_code;
            $seatName = $finalHistory->eventSeat->name ?? '';

            $finalMessage = $this->generateFinalMessage(
                !$userExists,
                !$history, // !history = vé vừa được tạo
                $seatWasUpdated
            );

            return [
                'status'  => true,
                'message' => $finalMessage['message'],
                'title'   => $finalMessage['title'],
                'data' => [
                    'ticket_code' => $ticketCode,
                    'seat_name' => $seatName,
                    'user_exists' => $userExists,
                ]
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Quick register failed: " . $e->getMessage());

            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
                'title' => __('event.validation.register_fail_title'),
            ];
        }
    }

    /**
     * Authenticate with phone number - Check if phone exists and send OTP if new
     *
     * @param string $phone
     * @param int $organizerId
     * @return array
     */
    /**
     * Send authentication code (OTP)
     * 
     * @param string $username
     * @param string $type enum: register, login, forgot_password
     * @param int $organizerId
     * @return array
     */
    public function sendAuthenticationCode(string $username, string $type, int $organizerId): array
    {
        try {
            // Check Rate Limit
            $limitCheck = $this->checkHoldingLimit('send_otp', $username);
            if (!$limitCheck['status']) {
                return $limitCheck;
            }

            $usernameType = $this->detectUsernameType($username);

            if ($usernameType === 'phone') {
                return $this->sendPhoneOtp($username, $type, $organizerId);
            }

            if ($usernameType === 'email') {
                return $this->sendEmailOtp($username, $type, $organizerId);
            }

            return [
                'status' => false,
                'message' => __('auth.error.invalid_username'),
            ];
        } catch (\Throwable $e) {
            Log::error('sendAuthenticationCode failed', ['error' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    private function sendPhoneOtp(string $phone, string $type, int $organizerId): array
    {
        // Check if user exists
        $user = User::where('phone', $phone)->where('organizer_id', $organizerId)->first();

        if ($type === 'register' && $user && $user->phone_verified_at) {
            return [
                'status' => false,
                'message' => __('auth.error.phone_already_registered'),
            ];
        }

        if (($type === 'login' || $type === 'forgot_password') && !$user) {
            return [
                'status' => false,
                'message' => __('auth.error.user_not_found'),
            ];
        }

        // Generate OTP
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Determine ZNS Template based on type
        $purpose = \App\Utils\Constants\PurposeZNS::REGISTER; // Default

        // Send via Zalo
        $zaloService = app(ZaloService::class);
        $result = $zaloService->sendOTP($phone, $otp, $purpose);

        if (!$result['success']) {
            return [
                'status' => false,
                'message' => $result['message'],
            ];
        }

        // Cache OTP
        $cacheKey = "otp:{$type}:{$phone}:{$organizerId}";
        Cache::put($cacheKey, [
            'otp' => $otp,
            'attempts' => 0,
            'ip_address' => request()->ip(),
        ], now()->addMinutes(10));

        return [
            'status' => true,
            'message' => __('auth.success.otp_sent'),
            'expire_minutes' => 10,
        ];
    }

    private function sendEmailOtp(string $email, string $type, int $organizerId): array
    {
        // Check if user exists check
        $user = User::where('email', $email)->where('organizer_id', $organizerId)->first();

        if ($type === 'register' && $user && $user->email_verified_at) {
            return [
                'status' => false,
                'message' => __('auth.error.email_already_registered'),
            ];
        }

        if (($type === 'login') && !$user) {
            // For login, if user not found, return error
            return [
                'status' => false,
                'message' => __('auth.error.user_not_found'),
            ];
        }

        // Generate OTP
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Send via Email
        $locale = request()->input('locate', 'vi');
        App::setLocale($locale);

        try {
            Mail::to($email)->send(new ResetPasswordMail($otp, $locale));
        } catch (\Throwable $e) {
            Log::error('Failed to send email OTP: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }

        // Cache OTP
        $cacheKey = "otp:{$type}:{$email}:{$organizerId}";
        Cache::put($cacheKey, [
            'otp' => $otp,
            'attempts' => 0,
            'ip_address' => request()->ip(),
        ], now()->addMinutes(10));

        return [
            'status' => true,
            'message' => __('auth.success.otp_sent'),
            'expire_minutes' => 10,
        ];
    }

    private function generateFinalMessage(bool $isNewUser, bool $isNewTicket, bool $seatWasUpdated): array
    {
        if ($isNewUser) {
            return [
                'title' => __('event.messages.register_success_title'),
                'message' => __('event.messages.new_user_new_ticket_message'),
            ];
        }

        if ($isNewTicket && $seatWasUpdated) {
            return [
                'title' => __('event.messages.ticket_granted_title'),
                'message' => __('event.messages.existing_user_new_ticket_message'),
            ];
        }

        if (!$isNewTicket) {
            return [
                'title' => __('event.messages.ticket_confirmed_title'),
                'message' => __('event.messages.existing_user_existing_ticket_message'),
            ];
        }

        return [
            'title' => __('common.messages.process_complete_title'),
            'message' => __('common.messages.process_complete_message'),
        ];
    }
    /**
     * Detect if username is email or phone
     * @param string $username
     * @return string 'email'|'phone'|'invalid'
     */
    public function detectUsernameType(string $username): string
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        // Simple regex for VN phone: starts with 0 or +84, followed by 9-10 digits
        if (preg_match('/^(\+84|0)[0-9]{9,10}$/', $username)) {
            return 'phone';
        }

        return 'invalid';
    }

    /**
     * Check rate limiting (Holding Limit)
     * Limit: 5 requests per hour per action/target/ip
     * Block duration: 5 minutes
     * 
     * @param string $action e.g., 'send_otp', 'login_fail'
     * @param string $target username or ip
     * @return array ['status' => bool, 'message' => string|null]
     */
    public function checkHoldingLimit(string $action, string $target): array
    {
        $ip = request()->ip();
        // Rate limit key: limit:{action}:{target}:{ip}
        $key = "auth_limit:{$action}:{$target}:{$ip}";
        $blockKey = "auth_block:{$action}:{$target}:{$ip}";

        if (Cache::has($blockKey)) {
            return [
                'status' => false,
                'message' => __('auth.error.too_many_attempts_wait', ['minutes' => 5]),
            ];
        }

        $attempts = Cache::get($key, 0);

        if ($attempts >= 5) {
            // Block for 5 minutes
            Cache::put($blockKey, true, now()->addMinutes(5));
            Cache::forget($key); // Reset attempts after blocking

            return [
                'status' => false,
                'message' => __('auth.error.too_many_attempts_wait', ['minutes' => 5]),
            ];
        }

        // Increment attempts and set expiry for 5 minutes window
        if ($attempts === 0) {
            Cache::put($key, 1, now()->addMinutes(5));
        } else {
            Cache::increment($key);
        }

        return ['status' => true];
    }
    /**
     * Verify OTP Code
     * 
     * @param string $username
     * @param string $code
     * @param int $organizerId
     * @param string $type
     * @return array
     */
    public function verifyCode(string $username, string $code, int $organizerId, string $type): array
    {
        try {
            $cacheKey = "otp:{$type}:{$username}:{$organizerId}";
            $tokenData = Cache::get($cacheKey);

            if (!$tokenData) {
                return [
                    'status' => false,
                    'message' => __('auth.error.otp_not_found'),
                ];
            }

            if ($tokenData['otp'] !== $code) {
                return [
                    'status' => false,
                    'message' => __('auth.error.invalid_otp'),
                ];
            }

            // Detect username type and find user
            $usernameType = $this->detectUsernameType($username);
            $user = null;

            if ($usernameType === 'phone') {
                $user = User::where('phone', $username)->where('organizer_id', $organizerId)->first();
            } elseif ($usernameType === 'email') {
                $user = User::where('email', $username)->where('organizer_id', $organizerId)->first();
            }

            if (!$user) {
                return [
                    'status' => false,
                    'message' => __('auth.error.user_not_found'),
                ];
            }

            // Clear OTP
            Cache::forget($cacheKey);

            // Handle different types
            if ($type === 'forgot_password') {
                // Generate reset token
                $resetToken = \Illuminate\Support\Str::uuid()->toString();
                $resetCacheKey = "reset_token:{$resetToken}";

                Cache::put($resetCacheKey, [
                    'user_id' => $user->id,
                    'organizer_id' => $organizerId,
                ], now()->addMinutes(5));

                return [
                    'status' => true,
                    'message' => __('auth.success.otp_verified'),
                    'reset_token' => $resetToken,
                ];
            }

            // For login/register types, mark as verified and create auth token
            if ($usernameType === 'phone') {
                $user->phone_verified_at = now();
                $user->save();
            } elseif ($usernameType === 'email') {
                $user->email_verified_at = now();
                $user->save();
            }

            // Create auth token
            $authToken = $user->createToken('api', expiresAt: now()->addDays(30))->plainTextToken;

            return [
                'status' => true,
                'message' => __('auth.success.otp_verified'),
                'token' => $authToken,
                'user' => $user,
            ];
        } catch (\Throwable $e) {
            Log::error('verifyCode error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    /**
     * Reset password using reset token
     * 
     * @param string $resetToken
     * @param string $password
     * @return array
     */
    public function resetPassword(string $resetToken, string $password): array
    {
        try {
            $cacheKey = "reset_token:{$resetToken}";
            $tokenData = Cache::get($cacheKey);

            if (!$tokenData) {
                return [
                    'status' => false,
                    'message' => __('auth.error.invalid_or_expired_token'),
                ];
            }

            $user = User::find($tokenData['user_id']);

            if (!$user || $user->organizer_id !== $tokenData['organizer_id']) {
                return [
                    'status' => false,
                    'message' => __('auth.error.user_not_found'),
                ];
            }

            // Update password
            $user->password = Hash::make($password);
            $user->save();

            // Clear reset token
            Cache::forget($cacheKey);

            return [
                'status' => true,
                'message' => __('auth.success.password_changed'),
            ];
        } catch (\Throwable $e) {
            Log::error('resetPassword error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    /**
     * Lock current user account
     * @return array
     */
    public function lockAccount(): array
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            if (!$user) {
                return [
                    'status' => false,
                    'message' => __('auth.error.user_not_found'),
                ];
            }

            $user->inactive = true;
            $user->save();
            $user->tokens()->delete();

            return [
                'status' => true,
                'message' => __('auth.success.lock_account_success'),
            ];
        } catch (\Throwable $e) {
            Log::error('lockAccount error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }
}
