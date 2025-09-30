<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\User;
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
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function login(array $data): array
    {
        try {
            $user = User::query()
                ->where('email', $data['email'])
                ->where('organizer_id', $data['organizer_id'])
                ->first();
            if (!$user || ! Hash::check($data['password'], $user->password)) {
                throw new ServiceException(__('auth.error.invalid_credentials'));
            }
            if (!$user->hasVerifiedEmail()) {
                throw new ServiceException(__('auth.error.unverified_email'));
            }
            $user->lang = $data['locate'] ?? Language::VI->value;
            $user->save();
            $token = $user->createToken('api',expiresAt: now()->addDays(30))->plainTextToken;
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
                'api.verification.verify',
                now()->addMinutes(60),
                ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]
            );
            Mail::raw(__('auth.success.verify_email_body') . " {$url}", fn($m) => $m->to($user->email)->subject('Verify Email'));
            DB::commit();
            return [
                'status' => true,
            ];
        }  catch (\Throwable $e) {
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
            $user->address= $data['address'] ?? null;
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
        }catch (\Throwable $e) {
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
            if (!$avatarPathNew){
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
            $user = User::query()->create([
                'name' => trim($data['name']),
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'organizer_id' => (int) $data['organizer_id'],
                'role' => RoleUser::CUSTOMER->value,
                'lang' => $data['lang'] ?? Language::VI->value,
                'phone' => $data['phone'],
                'email_verified_at' => now(),
                'phone_verified_at' => now()
            ]);

            DB::commit();
            return [
                'status' => true,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }
}
