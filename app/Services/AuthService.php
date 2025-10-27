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
use App\Models\EventSeat;
use App\Models\Organizer;
use App\Utils\Constants\CommonStatus;
use App\Utils\Constants\ConfigType;
use App\Utils\Constants\EventSeatStatus;
use App\Utils\Helper;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

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
            $user = User::query()
                ->where('email', $data['email'])
                ->whereHasActiveOrganizer((int) $data['organizer_id'])
                ->first();
            if (!$user || ! Hash::check($data['password'], $user->password)) {
                throw new ServiceException(__('auth.error.invalid_credentials'));
            }
            if (!$user->hasVerifiedEmail()) {
                throw new ServiceException(__('auth.error.unverified_email'));
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
            Log::debug($e->getMessage());
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            Log::debug($e->getMessage());
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
            $user = User::query()->create([
                'name' => trim($data['name']),
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'organizer_id' => (int) $data['organizer_id'],
                'role' => RoleUser::CUSTOMER->value,
                'lang' => request()->input('locate') ?? Language::VI->value,
            ]);
            $url = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]
            );
            Mail::raw(__('auth.success.verify_email_body') . " {$url}", fn($m) => $m->to($user->email)->subject('Verify Email'));
            DB::commit();
            return [
                'status' => true,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function editInfoUser(array $data)
    {
        $user = Auth::user();
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

    public function forgotPassword(array $data, string $locale = 'vi'): array
    {
        try {
            $user = User::where('email', $data['email'])->first();

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

    public function confirmPassword(array $data): array
    {
        try {
            $user = User::where('email', $data['email'])->first();

            $resetCode = UserResetCode::where('user_id', $user->id)
                ->where('email', $data['email'])
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

            $user->password = Hash::make($data['password']);
            $user->save();

            $resetCode->delete();

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
                ->with('eventSeat')
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
}
