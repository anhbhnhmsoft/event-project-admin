<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrganizerService;
use App\Utils\Constants\CommonStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class OrganizerController extends Controller
{
    protected OrganizerService $organizerService;

    public function __construct(OrganizerService $organizerService)
    {
        $this->organizerService = $organizerService;
    }

    public function getOrganizers(Request $request): JsonResponse
    {
        $keyword = $request->query('key');
        $limit = $request->integer('limit', 10);

        $organizers = $this->organizerService->getOptions([
            'keyword' => $keyword,
            'status' => CommonStatus::ACTIVE->value,
        ], $limit);
        $organizersMap = array_map(function ($item) {
            return [
                'id' => (string) $item['id'],
                'name' => (string) $item['name'],
            ];
        }, $organizers);

        return response()->json([
            'message' => __('organizer.success.get_success'),
            'data' => $organizersMap,
        ], 200);
    }

    public function viewRegisterNewOrganizer()
    {
        return Inertia::render('RegisterOrganizer', [
            'csrf_token' => csrf_token(),
        ])->rootView('layout.auth');
    }

    public function registerNewOrganizer(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|regex:/^([0-9\s\-\+\(\)]*)$/',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => __('auth.validation.name_required'),
            'email.required' => __('auth.validation.email_required'),
            'email.email' => __('auth.validation.email_email'),
            'email.unique' => __('auth.validation.email_unique'),
            'phone.required' => __('auth.validation.phone_required'),
            'phone.regex' => __('auth.validation.phone_regex'),
            'password.required' => __('auth.validation.password_required'),
            'password.min' => __('auth.validation.password_min'),
            'password.confirmed' => __('auth.validation.confirm_password_same'),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Register organizer
        $result = $this->organizerService->registerNewOrganizer($validator->validated());

        if (!$result['status']) {
            return back()
                ->withErrors(['email' => $result['message'] ?? __('organizer.error.register_failed')])
                ->withInput();
        }

        return redirect()->back()->with('success', $result['message'] ?? __('organizer.success.register_success'));
    }
}
