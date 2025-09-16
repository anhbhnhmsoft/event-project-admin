<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Mail\VerifyEmailMail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $v = $request->validate([
            'email' => 'required|email',
            'password' => ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/'],
            'locate' => ['nullable', 'in:vi,en'],
        ]);

        $result = $this->authService->login($v);
        $user = $result['user'];
        $token = $result['token'];

        return response()->json([
            'message' => __('auth.success.login_success'),
            'data' => [
                'token' => $token,
                'user' => new UserResource($user),
            ],
        ], 200);
    }

    public function register(Request $request)
    {
        $v = $request->validate([
            'name' => ['required', 'string', 'min:4', 'max:255'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/'],
            'confirm_password' => ['required'],
            'organizer_id' => ['required', 'integer'],
        ]);

        $this->authService->register($v);

        return response()->json([
            'message' => __('auth.success.register_success'),
            'data' => ['status' => true],
        ], 201);
    }

    public function verifyEmail(Request $request)
    {
        $user = User::find($request->route('id'));
        if (! $user) {
            return response()->json(['message' => __('auth.error.email_not_found'), 'data' => null], 404);
        }

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => __('auth.error.invalid_code'), 'data' => null], 422);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => __('auth.success.already_verified'), 'data' => ['status' => true]], 200);
        }

        $user->markEmailAsVerified();

        return response()->json(['message' => __('auth.success.verify_success'), 'data' => ['status' => true]], 200);
    }

    public function resendVerify(Request $request)
    {
        $v = $request->validate([
            'email' => ['required', 'email'],
            'locate' => ['sometimes', 'string', 'in:vi,en'],
        ]);

        $locale = $v['locate'] ?? null;

        $user = User::where('email', $v['email'])->first();

        if (! $user) {
            return response()->json(['message' => __('auth.error.email_not_found'), 'data' => null], 404);
        }
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => __('auth.success.already_verified'), 'data' => ['status' => true]], 200);
        }

        $url = URL::temporarySignedRoute(
            'api.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]
        );

        Mail::to($user->email)->send(new VerifyEmailMail($url, $locale));

        return response()->json(['message' => __('auth.success.verify_sent'), 'data' => ['status' => true]], 200);
    }

    public function forgotPassword(Request $request)
    {
        $v = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'locate' => ['sometimes', 'string', 'in:vi,en'],
        ]);

        $locale = $v['locate'] ?? null;

        $this->authService->forgotPassword($v, $locale);

        return response()->json([
            'message' => __('auth.success.reset_sent'),
            'data' => ['status' => true],
        ], 200);
    }

    public function confirmPassword(Request $request)
    {
        $v = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/'],
            'confirm_password' => ['required', 'same:password'],
        ]);

        $this->authService->confirmPassword($v);

        return response()->json([
            'message' => __('auth.success.password_changed'),
            'data' => ['status' => true],
        ], 200);
    }

    public function getUserInfo(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => __('auth.error.unauthorized'),
                'data' => null,
            ], 401);
        }

        return response()->json([
            'message' => __('auth.success.user_info'),
            'data' => new UserResource($user),
        ], 200);
    }
}
