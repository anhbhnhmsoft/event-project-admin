<?php

namespace App\Services;

use App\Models\UserNotification;
use App\Models\UserDevice;
use App\Utils\Constants\UserNotificationStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function userNotificationPaginator(array $filters = [], string $sortBy = '', int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        try {
            return UserNotification::filter($filters)->sortBy($sortBy)
                ->paginate(perPage: $limit, page: $page);
        }  catch (\Exception $e) {
            return new LengthAwarePaginator([], 0, $limit, $page);
        }
    }

    public function getNotificationUnread($userId): int
    {
        return UserNotification::query()->where('user_id', $userId)
            ->where('status', UserNotificationStatus::SENT->value)
            ->count();
    }

    public function markAsRead(int $userId, int $notificationId): array
    {
        try {
            UserNotification::query()->where('user_id', $userId)
                ->where('id', $notificationId)
                ->update(['status' => UserNotificationStatus::READ->value]);
            return [
                'status' => true,
                'message' => __('common.mark_as_read_success'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function markAllAsRead(int $userId): array
    {
        try {
            UserNotification::query()->where('user_id', $userId)
                ->where('status', UserNotificationStatus::SENT->value)
                ->update(['status' => UserNotificationStatus::READ->value]);
            return [
                'status' => true,
                'message' => __('common.mark_as_read_success'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function storePushToken(int $userId, array $data): array
    {
        try {
            UserDevice::query()->updateOrCreate(
                [
                    'expo_push_token' => $data['expo_push_token'],
                ],
                [
                    'user_id' => $userId,
                    'expo_push_token' => $data['expo_push_token'],
                    'device_id' => $data['device_id'] ?? null,
                    'device_type' => $data['device_type'] ?? 'ios',
                    'is_active' => true,
                    'last_seen_at' => now(),
                ]
            );

            return [
                'status' => true,
                'message' => __('common.push_token_saved'),
            ];
        } catch (\Exception $e) {
            Log::error('error save token',['ex' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

}


