<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Mail\VerifyEmailMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/'],
            'organizer_id' => ['required', 'integer', 'exists:organizers,id'],
        ], [
            'email.required' => __('auth.validation.email_required'),
            'email.email' => __('auth.validation.email_email'),
            'password.required' => __('auth.validation.password_required'),
            'password.min' => __('auth.validation.password_min'),
            'password.regex' => __('auth.validation.password_regex'),
            'organizer_id.required' => __('auth.validation.organizer_id_required'),
            'organizer_id.integer' => __('auth.validation.organizer_id_integer'),
            'organizer_id.exists' => __('auth.validation.organizer_id_exists'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('auth.error.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->authService->login($validator->getData());

        if ($result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 422);
        }
        $user = $result['user'];
        $token = $result['token'];

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:4', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users','email')->where(function($query) use ($request) {
                return $query->where('organizer_id', $request->input('organizer_id'));
            })],
            'password' => ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/'],
            'confirm_password' => ['required', 'same:password'],
            'organizer_id' => ['required', 'integer', 'exists:organizers,id'],
        ], [
            'name.required' => __('auth.validation.name_required'),
            'name.min' => __('auth.validation.name_min'),
            'name.max' => __('auth.validation.name_max'),
            'email.required' => __('auth.validation.email_required'),
            'email.email' => __('auth.validation.email_email'),
            'password.required' => __('auth.validation.password_required'),
            'password.min' => __('auth.validation.password_min'),
            'password.regex' => __('auth.validation.password_regex'),
            'confirm_password.required' => __('auth.validation.confirm_password_required'),
            'confirm_password.same' => __('auth.validation.confirm_password_same'),
            'organizer_id.required' => __('auth.validation.organizer_id_required'),
            'organizer_id.integer' => __('auth.validation.organizer_id_integer'),
            'organizer_id.exists' => __('auth.validation.organizer_id_exists'),
            'email.unique' => __('auth.validation.email_unique'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('auth.error.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->authService->register($validator->validated());

        if ($result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'message' => __('auth.success.register_success'),
        ], 200);
    }

    public function verifyEmail(Request $request)
    {
        $user = User::find($request->route('id'));
        if (! $user) {
            return response()->json([
                'message' => __('auth.error.email_not_found'),
            ],422);
        }

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => __('auth.error.invalid_code'),
            ], 422);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => __('auth.success.already_verified'),
            ], 200);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'message' => __('auth.success.verify_success'),
        ], 200);
    }

    public function resendVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'locate' => ['nullable', 'string', 'in:vi,en'],
        ], [
            'email.required' => __('auth.validation.email_required'),
            'email.email' => __('auth.validation.email_email')
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('auth.error.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $locale = $validated['locate'] ?? null;

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => __('auth.error.email_not_found'),
            ], 422);
        }
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => __('auth.success.already_verified'),
            ], 200);
        }

        $url = URL::temporarySignedRoute(
            'api.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]
        );

        Mail::to($user->email)->send(new VerifyEmailMail($url, $locale));

        return response()->json([
            'message' => __('auth.success.verify_sent'),
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email'],
            'locate' => ['nullable', 'string', 'in:vi,en'],
        ], [
            'email.required' => __('auth.validation.email_required'),
            'email.email' => __('auth.validation.email_email'),
            'email.exists' => __('auth.validation.email_error'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('auth.error.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $locale = $validated['locate'] ?? null;

        $result = $this->authService->forgotPassword($validated, $locale);

        if (isset($result['status']) && $result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'message' => __('auth.success.reset_sent'),
            'data' => ['status' => true],
        ], 200);
    }

    public function confirmPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/'],
            'confirm_password' => ['required', 'same:password'],
        ], [
            'email.required' => __('auth.validation.email_required'),
            'email.email' => __('auth.validation.email_email'),
            'code.required' => __('auth.validation.code_required'),
            'code.size' => __('auth.validation.code_size'),
            'password.required' => __('auth.validation.password_required'),
            'password.min' => __('auth.validation.password_min'),
            'password.regex' => __('auth.validation.password_regex'),
            'confirm_password.required' => __('auth.validation.confirm_password_required'),
            'confirm_password.same' => __('auth.validation.confirm_password_same'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('auth.error.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->authService->confirmPassword($validator->validated());

        if (isset($result['status']) && $result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'message' => __('auth.success.password_changed'),
        ], 200);
    }

    public function getUserInfo(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => __('auth.error.unauthorized'),
            ], 401);
        }

        return response()->json([
            'message' => __('auth.success.user_info'),
            'data' => new UserResource($user),
        ], 200);
    }
}
