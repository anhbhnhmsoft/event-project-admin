<?php

namespace App\Services;

use App\Models\Config;
use App\Models\Organizer;
use App\Models\User;
use App\Utils\Constants\CommonStatus;
use App\Utils\Constants\ConfigName;
use App\Utils\Constants\ConfigType;
use App\Utils\Constants\Language;
use App\Utils\Constants\RoleUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class OrganizerService
{
    public function getActive(): Collection
    {
        return Organizer::whereNull('deleted_at')->get();
    }

    public function getActiveOptions(): array
    {
        return $this->getActive()->pluck('name', 'id')->toArray();
    }

    public function filter(array $filters = [])
    {
        $query = Organizer::query();
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);
            $query->where('name', 'like', '%' . $keyword . '%');
        }
        return $query;
    }

    public function getOptions(array $filters = [], int $limit = 10): array
    {
        try {
            $query = $this->filter($filters);
            return $query->limit($limit)->select(['id', 'name'])->get()->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getOrganizerDetail($id): array
    {
        try {
            $organizer = Organizer::query()
                ->with([
                    'users',
                    'plansActive'
                ])
                ->find($id);

            if (!$organizer) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            return [
                'status' => true,
                'organizer' => $organizer,
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function initOrganizer(array $data): array
    {
        DB::beginTransaction();

        try {
            $organizer = Organizer::query()->create($data);

            $configs = [
                [
                    'config_key' => ConfigName::CLIENT_ID_APP->value,
                    'config_type' => ConfigType::STRING->value,
                    'config_value' => '',
                    'description' => 'ID ứng dụng ở kênh thanh toán',
                    'organizer_id' => $organizer->id
                ],
                [
                    'config_key' => ConfigName::API_KEY->value,
                    'config_type' => ConfigType::STRING->value,
                    'config_value' => '',
                    'description' => 'Mã API ở kênh thanh toán',
                    'organizer_id' => $organizer->id
                ],
                [
                    'config_key' => ConfigName::CHECKSUM_KEY->value,
                    'config_type' => ConfigType::STRING->value,
                    'config_value' => '',
                    'description' => 'Mã CHECKSUM ở kênh thanh toán',
                    'organizer_id' => $organizer->id
                ],
                [
                    'config_key' => ConfigName::LINK_ZALO_SUPPORT->value,
                    'config_type' => ConfigType::STRING->value,
                    'config_value' => 'https://zalo.me/your-support-link',
                    'description' => 'Link hỗ trợ Zalo của hệ thống',
                    'organizer_id' => $organizer->id
                ],
                [
                    'config_key' => ConfigName::LINK_FACEBOOK_SUPPORT->value,
                    'config_type' => ConfigType::STRING->value,
                    'config_value' => 'https://facebook.com/your-support-page',
                    'description' => 'Link trang hỗ trợ Facebook của hệ thống',
                    'organizer_id' => $organizer->id
                ],
            ];

            Config::query()->insert($configs);

            DB::commit();

            return [
                'status'  => true,
                'data' => $organizer
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Failed to initialize organizer. Data: " . json_encode($data) . " Error: " . $e->getMessage());

            return [
                'status'  => false,
                'message' => __('organizer.error.init_failed'),
            ];
        }
    }
    public function getOrganizer($id)
    {
        return Organizer::with(['plansActive'])->find($id);
    }

    public function registerNewOrganizer($data)
    {
        DB::beginTransaction();
        try {

            $organizer = Organizer::create([
                'name'   => $data['name'],
                'status' => CommonStatus::ACTIVE->value,
            ]);

            $user = User::query()->create(
                [
                    'name'         => $data['name'],
                    'email'        => $data['email'],
                    'password'     => Hash::make($data['password']),
                    'role'         => RoleUser::ADMIN->value,
                    'phone'        => $data['phone'] ?? null,
                    'organizer_id' => $organizer->id,
                    'lang'         => Language::VI->value,
                ]
            );
            DB::commit();
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $user->getKey(),
                    'hash' => sha1($user->getEmailForVerification())
                ]
            );

            Mail::send('emails.verify-email', ['url' => $verificationUrl, 'user' => $user], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Xác thực tài khoản - ' . config('app.name'));
            });

            return [
                'status'  => true,
                'message' => __('organizer.success.register_success'),
                'data'    => [
                    'organizer_id' => $organizer->id,
                    'user_id'      => $user->id,
                ],
            ];
        } catch (\Throwable $th) {

            DB::rollBack();
            Log::error('Register organizer failed: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString(),
                'data'  => $data,
            ]);

            return [
                'status'  => false,
                'message' => __('organizer.error.register_failed'),
            ];
        }
    }
    public function registerOrganizerForSignup(array $organizerData, array $userData): array
    {
        try {
            $organizer = Organizer::create([
                'name' => $organizerData['name'],
                'status' => $organizerData['status'] ?? CommonStatus::ACTIVE->value ,
            ]);

            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'],
                'password' => Hash::make($userData['password']),
                'organizer_id' => $organizer->id,
                'lang' => Language::VI->value,
                'role' => RoleUser::ADMIN->value,
                'email_verified_at' => now()
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

            return [
                'status' => true,
                'organizer' => $organizer,
                'user' => $user
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteOrganizerAndUsers(int $organizerId): void
    {
        $organizer = Organizer::find($organizerId);
        
        if ($organizer) {
            User::where('organizer_id', $organizerId)->delete();
            $organizer->forceDelete();
        }
    }

    public function activateOrganizer(int $organizerId): bool
    {
        $organizer = Organizer::find($organizerId);
        if ($organizer) {
            return $organizer->update(['status' => true]);
        }
        return false;
    }

    /**
     * Complete signup flow: register organizer, user, authenticate, and send verification email
     * 
     * @param array $organizerData
     * @param array $userData (must include 'password' in plain text)
     * @return array ['success' => bool, 'organizer' => Organizer|null, 'user' => User|null, 'error' => string|null]
     */
    public function completeSignupFlow(array $organizerData, array $userData): array
    {
        try {
            DB::beginTransaction();

            // Register organizer and user
            $result = $this->registerOrganizerForSignup($organizerData, $userData);
            $organizer = $result['organizer'];
            $user = $result['user'];

            // Attempt authentication
            $authenticated = Auth::attempt([
                'email' => $user->email,
                'password' => $userData['password']
            ]);

            if (!$authenticated) {
                throw new \Exception('Authentication failed after registration');
            }

            // Send verification email (non-blocking)
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                Log::error('Failed to send verification email during signup', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'organizer' => $organizer,
                'user' => $user,
                'error' => null
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Complete signup flow failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'organizer' => null,
                'user' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cleanup signup: logout and delete organizer with users
     * 
     * @param int $organizerId
     * @return void
     */
    public function cleanupSignup(int $organizerId): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        
        $this->deleteOrganizerAndUsers($organizerId);
    }
}
