<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\User;
use App\Utils\Constants\Language;
use App\Utils\Constants\RoleUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\App;
use App\Models\UserResetCode;
use App\Mail\ResetPasswordMail;

class AuthService
{
    public function login(array $data): array
    {
        try {
            $user = User::where('email', $data['email'])->first();

            if (! $user || ! Hash::check($data['password'], $user->password)) {
                return [
                    'status' => false,
                    'message' => __('auth.error.invalid_credentials'),
                ];
            }

            $user->lang = $data['locate'] ?? Language::VI->value;
            $user->save();
            $token = $user->createToken('api')->plainTextToken;

            return [
                'token' => $token,
                'user' => $user,
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

    public function register(array $data): array
    {
        try {
            $user = new User();
            $user->name = trim($data['name']);
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->organizer_id = (int) $data['organizer_id'];
            $user->role = RoleUser::CUSTOMER->value;
            $user->lang = $data['locate'] ?? Language::VI->value;
            $user->save();
            
            $url = URL::temporarySignedRoute(
                'api.verification.verify',
                now()->addMinutes(60),
                ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]
            );
            
            Mail::raw(__('auth.success.verify_email_body') . " {$url}", fn($m) => $m->to($user->email)->subject('Verify Email'));

            return [
                'status' => true,
                'message' => __('auth.success.register_success'),
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
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now()]);

            return $count;
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
}


