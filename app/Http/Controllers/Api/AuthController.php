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

    public function supportLink(Request $request)
    {
        $config = $this->authService->getSupportLink();
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => $config,
        ], 200);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => ['required', 'string', 'min:8'],
            'organizer_id' => ['required', 'integer', 'exists:organizers,id'],
        ], [
            'email.required' => __('auth.validation.email_required'),
            'email.email' => __('auth.validation.email_email'),
            'password.required' => __('auth.validation.password_required'),
            'password.min' => __('auth.validation.password_min'),
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

    public function edit(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:4', 'max:255'],
            'phone' => [
                'nullable',
                'regex:/^0[0-9]{9,10}$/',
                Rule::unique('users', 'phone')->where(function ($query) use ($user) {
                    return $query->where('organizer_id', $user->organizer_id)
                        ->where('id', '!=', $user->id);
                })
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'introduce' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8'],
            'confirm_password' => ['nullable', 'same:password'],
       ],[
            'name.required' => __('auth.validation.name_required'),
            'name.string' => __('auth.validation.name_required'),
            'name.min' => __('auth.validation.name_min'),
            'name.max' => __('auth.validation.name_max'),
            'phone.regex' => __('auth.validation.phone_regex'),
            'phone.unique' => __('auth.validation.phone_unique'),
            'address.string' => __('auth.validation.address_invalid'),
            'address.max' => __('auth.validation.address_max'),
            'introduce.string' => __('auth.validation.introduce_invalid'),
            'password.string' => __('auth.validation.password_required'),
            'password.min' => __('auth.validation.password_min'),
            'confirm_password.same' => __('auth.validation.confirm_password_same'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('auth.error.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }
        $data = $validator->validated();
        $result = $this->authService->editInfoUser($data);
        if ($result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 500);
        }
        return response()->json([
            'message' => __('common.common_success.update_success'),
            'data' => UserResource::make($result['data']),
        ], 200);
    }

    public function editAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg|max:10240'
       ],[
            'file.required' => __('auth.validation.avatar_invalid'),
            'file.image' => __('auth.validation.avatar_invalid'),
            'file.mimes' => __('auth.validation.avatar_invalid'),
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => __('auth.error.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }
        $result = $this->authService->editInfoAvatar($request->file('file'));
        if ($result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 500);
        }
        return response()->json([
            'message' => __('common.common_success.update_success'),
            'data' => UserResource::make($result['data']),
        ], 200);
    }

    public function deleteAvatar()
    {
        $result = $this->authService->deleteAvatar();
        if ($result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 500);
        }
        return response()->json([
            'message' => __('common.common_success.update_success'),
            'data' => UserResource::make($result['data']),
        ], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:4', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users','email')->where(function($query) use ($request) {
                return $query->where('organizer_id', $request->input('organizer_id'));
            })],
            'password' => ['required', 'string', 'min:8'],
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
            return view('emails.auth.response-verify', [
                'status' => 'error',
                'message' => __('auth.error.email_not_found'),
            ]);
        }

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return view('emails.auth.response-verify', [
                'status' => 'error',
                'message' => __('auth.error.invalid_code'),
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return view('emails.auth.response-verify', [
                'status' => 'warning',
                'message' => __('auth.success.already_verified'),
            ]);
        }

        $user->markEmailAsVerified();

        return view('emails.auth.response-verify', [
            'status' => 'success',
            'message' => __('auth.success.verify_success'),
        ]);
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
            'verification.verify',
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
        $user = $request->user()->load('activeMemberships');
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

    public function setLang(Request $request)
    {
        $user = $request->user();
        $lang = $request->string('lang','vi')->toString();
        $status = $this->authService->setLanguageUser($user, $lang);
        if ($status['status'] === false) {
            return response()->json([
                'message' => $status['message'],
            ], 500);
        }
        return response()->json([
            'message' => $status['message'],
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => __('auth.success.logout_success'),
        ], 200);
    }

    /**
     * -- Khóa tài khỏan của người dùng
     * @return JsonResponse
     */
    public function lockAccount()
    {
        $result = $this->authService->lockAccount();
        if (isset($result['status']) && $result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 422);
        }
        return response()->json([
            'message' => __('auth.success.lock_account_success'),
        ], 200);
    }
}
