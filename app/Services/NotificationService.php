<?php

namespace App\Services;

use App\Models\UserNotification;
use App\Models\UserDevice;
use App\Utils\Constants\UserNotificationStatus;
use Illuminate\Support\Facades\Http;

class NotificationService
{
    public function listForUser(int $userId, array $datas, int $page, int $limit): array
    {
        try {
            $query = UserNotification::query()
                ->where('user_id', $userId)
                ->with(['organizer', 'event']);

            if (isset($datas['status'])) {
                $query->where('status', $datas['status']);
            }

            if (isset($datas['notification_type'])) {
                $query->where('notification_type', $datas['notification_type']);
            }

            $notifications = $query->orderBy('created_at', 'desc')
                ->paginate(perPage: $limit, page: $page);

            $unreadCount = UserNotification::where('user_id', $userId)
                ->where('status', UserNotificationStatus::SENT->value)
                ->count();

            return [
                'status' => true,
                'message' => __('common.common_success.get_success'),
                'data' => $notifications,
                'unread_count' => $unreadCount,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function markAsRead(int $userId, int $notificationId): array
    {
        try {
            $updated = UserNotification::where('user_id', $userId)
                ->where('id', $notificationId)
                ->update(['status' => UserNotificationStatus::READ->value]);

            return [
                'status' => true,
                'message' => __('common.mark_as_read_success'),
                'data' => $updated,
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
            $updated = UserNotification::where('user_id', $userId)
                ->where('status', '!=', UserNotificationStatus::READ->value)
                ->update(['status' => UserNotificationStatus::READ->value]);

            return [
                'status' => true,
                'message' => __('common.mark_as_read_success'),
                'data' => $updated,
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
            UserDevice::updateOrCreate(
                [
                    'user_id' => $userId,
                    'expo_push_token' => $data['expo_push_token'],
                ],
                [
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
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function sendPushNotifications(int $notificationId): array
    {
        try {
            $notification = UserNotification::find($notificationId);
            
            if (!$notification) {
                return [
                    'status' => false,
                    'message' => 'Notification not found',
                ];
            }

            if ($notification->status !== UserNotificationStatus::SENT->value) {
                return [
                    'status' => false,
                    'message' => 'Notification already processed',
                ];
            }

            $devices = UserDevice::where('user_id', $notification->user_id)
                ->where('is_active', true)
                ->get();

            if ($devices->isEmpty()) {
                return [
                    'status' => false,
                    'message' => 'No active devices found for user',
                ];
            }

            $sentCount = 0;
            $success = true;
            
            foreach ($devices as $device) {
                try {
                    $result = $this->sendExpoPushNotification(
                        $device->expo_push_token,
                        $notification->title,
                        $notification->description,
                        [
                            'notification_id' => $notification->id,
                            'event_id' => $notification->event_id,
                            'organizer_id' => $notification->organizer_id,
                            'notification_type' => $notification->notification_type,
                        ]
                    );
                    
                    if ($result['success']) {
                        $sentCount++;
                    } else {
                        $success = false;
                    }
                } catch (\Exception $e) {
                    $success = false;
                }
            }

            return [
                'status' => true,
                'message' => $success ? 'Push notifications sent successfully' : 'Some push notifications failed',
                'data' => [
                    'sent_count' => $sentCount,
                    'total_devices' => $devices->count(),
                    'status' => $notification->status,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    private function sendExpoPushNotification(string $expoPushToken, string $title, string $body, array $data = []): array
    {
        try {
            $plainBody  = trim(strip_tags($body ?? ''));

            $message = [
                'to' => $expoPushToken,
                'title' => $title,
                'body' => $plainBody,
                'data' => $data,
                'sound' => 'default',
                'badge' => 1,
            ];

            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Accept-encoding' => 'gzip, deflate',
                    'Content-Type' => 'application/json',
                ])
                ->post('https://exp.host/--/api/v2/push/send', [$message]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Kiểm tra nếu có lỗi trong response
                if (isset($responseData[0]['status']) && $responseData[0]['status'] === 'error') {
                    return [
                        'success' => false,
                        'error' => $responseData[0]['message'] ?? 'Unknown error',
                    ];
                }

                return [
                    'success' => true,
                    'response' => $responseData,
                ];
            }

            return [
                'success' => false,
                'error' => 'HTTP error: ' . $response->status(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function createAndSendNotification(int $userId, int $organizerId, int $eventId, string $title, string $description, int $notificationType, array $data = []): array
    {
        try {
            $notification = UserNotification::create([
                'user_id' => $userId,
                'organizer_id' => $organizerId,
                'event_id' => $eventId,
                'title' => $title,
                'description' => $description,
                'data' => $data,
                'notification_type' => $notificationType,
                'status' => UserNotificationStatus::SENT->value,
            ]);

            $pushResult = $this->sendPushNotifications($notification->id);

            return [
                'status' => true,
                'message' => 'Notification created and sent successfully',
                'data' => [
                    'notification' => $notification,
                    'push_result' => $pushResult,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }
}


