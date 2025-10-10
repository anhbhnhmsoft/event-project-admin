<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use App\Utils\Constants\UserNotificationStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $page = $request->integer('page', 1);
        $limit = $request->integer('limit', 10);
        $notifications = $this->notificationService->userNotificationPaginator(
            filters: [
                'user_id' => $request->user()->id,
                'statuses' => [UserNotificationStatus::SENT->value,UserNotificationStatus::READ->value],
            ],
            page: $page,
            limit: $limit
        );

        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => NotificationResource::collection($notifications),
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage()
            ],
        ], 200);
    }

    public function getUnReadCount(Request $request): \Illuminate\Http\JsonResponse
    {
        $unread = $this->notificationService->getNotificationUnread($request->user()->id);
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => [
                'unread' => $unread
            ],
        ], 200);
    }

    public function markAsRead(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => [
                'required',
                'exists:user_notifications,id',
            ],
        ], [
            'id.required' => __('common.common_error.data_not_found'),
            'id.exists' => __('common.common_error.data_not_found'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('common.common_error.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }
        $data = $validator->getData();

        $result = $this->notificationService->markAsRead($request->user()->id, $data['id']);

        if ($result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'message' => $result['message'],
        ], 200);
    }

    public function markAllAsRead(Request $request): \Illuminate\Http\JsonResponse
    {
        $result = $this->notificationService->markAllAsRead($request->user()->id);

        if ($result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'message' => $result['message'],
        ], 200);
    }

    public function storePushToken(Request $request): \Illuminate\Http\JsonResponse
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

        $validated = $validator->getData();

        $result = $this->notificationService->storePushToken($request->user()->id, $validated);

        if ($result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'message' => $result['message'],
        ], 200);
    }
}
