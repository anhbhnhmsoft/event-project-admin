<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $page  = $request->integer('page', 1);
        $limit = $request->integer('limit', 10);
        $filters = $request->only(['status', 'notification_type']);
        $result = $this->notificationService->listForUser($user->id, $filters, $page, $limit);

        if ($result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'message' => $result['message'],
            'data' => NotificationResource::collection($result['data']),
            'unread_count' => $result['unread_count'],
            'pagination' => [
                'total' => $result['data']->total(),
                'per_page' => $result['data']->perPage(),
                'current_page' => $result['data']->currentPage(),
                'last_page' => $result['data']->lastPage()
            ],
        ], 200);
    }

    public function markAsRead(Request $request)
    {
        $user = Auth::user();
        $notificationId = (int) $request->input('notification_id');
        $result = $this->notificationService->markAsRead($user->id, $notificationId);

        if ($result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ], 200);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();

        $result = $this->notificationService->markAllAsRead($user->id);

        if ($result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ], 200);
    }

    public function storePushToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expo_push_token' => ['required', 'string'],
            'device_id' => ['nullable', 'string'],
            'device_type' => ['nullable', 'string', 'in:ios,android'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('common.common_error.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $validated = $validator->validated();

        $result = $this->notificationService->storePushToken($user->id, $validated);

        if ($result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'message' => $result['message'],
        ], 200);
    }

    public function sendNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'organizer_id' => ['required', 'integer', 'exists:organizers,id'],
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'notification_type' => ['required', 'integer'],
            'data' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('common.common_error.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $result = $this->notificationService->createAndSendNotification(
            $validated['user_id'],
            $validated['organizer_id'],
            $validated['event_id'],
            $validated['title'],
            $validated['description'],
            $validated['notification_type'],
            $validated['data'] ?? []
        );

        if ($result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ], 200);
    }

    public function testSendNotification(Request $request, int $userId)
    {
        $result = $this->notificationService->createAndSendNotification(
            $userId,
            1,
            250918033855337395,
            'Test Notification',
            'test notification description',
            1,
            ['test' => true]
        );

        if ($result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'message' => 'Test notification sent successfully!',
            'data' => $result['data'],
        ], 200);
    }
}
