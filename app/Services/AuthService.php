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
                throw new ServiceException('auth.error.invalid_credentials', 422);
            }

            $user->lang = $data['locate'] ?? Language::VI->value;
            $user->save();
            $token = $user->createToken('api')->plainTextToken;

            return [
                'token' => $token,
                'user' => $user,
            ];
        } catch (ServiceException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new ServiceException($e->getMessage(), 500);
        }
    }

    public function register(array $data): void
    {
        try {
            $exists = User::where('email', $data['email'])->exists();
            if ($exists) {
                throw new ServiceException('auth.error.email_duplicate', 422);
            }
            if ($data['password'] !== $data['confirm_password']) {
                throw new ServiceException('auth.error.password_not_match', 422);
            }
            if (! DB::table('organizers')->where('id', $data['organizer_id'])->exists()) {
                throw new ServiceException('auth.error.organizer_not_found', 422);
            }

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
        } catch (ServiceException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new ServiceException($e->getMessage(), 500);
        }
    }

    public function forgotPassword(array $data, string $locale = 'vi'): void
    {
        try {
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                throw new ServiceException('auth.error.email_not_found', 422);
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

            UserResetCode::where('user_id', $user->id)
                ->where('email', $data['email'])
                ->where('code', $code)
                ->first();

            App::setLocale($locale);
            
            Mail::to($user->email)->send(new ResetPasswordMail($code, $locale));
        } catch (ServiceException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new ServiceException($e->getMessage(), 500);
        }
    }

    public function confirmPassword(array $data): void
    {
        try {
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                throw new ServiceException('auth.error.email_not_found', 422);
            }

            UserResetCode::withTrashed()
                ->where('user_id', $user->id)
                ->where('email', $data['email'])
                ->get();

            $resetCode = UserResetCode::where('user_id', $user->id)
                ->where('email', $data['email'])
                ->where('code', $data['code'])
                ->where('expires_at', '>', now())
                ->whereNull('deleted_at')
                ->first();

            if (!$resetCode) {
                throw new ServiceException('auth.error.invalid_code', 422);
            }

            $user->password = Hash::make($data['password']);
            $user->save();

            $resetCode->delete();
        } catch (ServiceException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new ServiceException($e->getMessage(), 500);
        }
    }

    public function checkExpiresAtUser(): int
    {
        try {
            $count = UserResetCode::where('expires_at', '<', now())
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now()]);

            return $count;
        } catch (\Throwable $e) {
            throw new ServiceException($e->getMessage(), 500);
        }
    }
}


