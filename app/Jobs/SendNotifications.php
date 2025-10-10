<?php

namespace App\Jobs;

use App\Models\UserDevice;
use App\Models\UserNotification;
use App\Utils\Constants\UserNotificationStatus;
use App\Utils\DTO\NotificationPayload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendNotifications implements ShouldQueue
{
    use Queueable;

    private mixed $nodeSendNotificationUrl;
    private NotificationPayload $payload;
    private mixed $accessToken;
    private array $userIds;

    /**
     * Create a new job instance.
     */
    public function __construct(NotificationPayload $payload, array $userIds)
    {
        $this->payload = $payload;
        $this->userIds = $userIds;
        $this->nodeSendNotificationUrl = config('services.node_server.notification_url');
        $this->accessToken = config('services.node_server.access_token');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Tối ưu: Lấy tất cả token cần thiết trong 1 query
        $devices = UserDevice::query()
            ->whereIn('user_id', $this->userIds)
            ->where('is_active', 1)
            ->get();
        $tokensByUser = $devices->groupBy('user_id')
            ->map(fn($group) => $group->pluck('expo_push_token')->toArray())
            ->toArray();
        DB::beginTransaction();
        try {
            $batch = array_reduce($this->userIds, function ($carry, $userId) use ($tokensByUser) {
                $tokens = $tokensByUser[$userId] ?? [];
                if (empty($tokens)) {
                    // Không có token, bỏ qua người dùng này
                    return $carry;
                }
                // Tạo notification trước
                $notification = UserNotification::query()->create([
                    'user_id' => $userId,
                    'title' => $this->payload->title,
                    'description' => $this->payload->description,
                    'data' => json_encode($this->payload->data),
                    'notification_type' => $this->payload->notificationType->value,
                    'status' => UserNotificationStatus::PENDING->value,
                ]);
                // Thêm vào Batch gửi đi
                $carry[] = [
                    'notification_id' => (string)$notification->id,
                    'user_id' => (string)$userId,
                    'tokens' => $tokens
                ];
                return $carry;
            });
            if (empty($batch)) {
                DB::rollBack();
                Log::debug('SendNotifications: Không có batch để gửi');
                return;
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('SendNotifications: Có lỗi xảy ra lúc insert noti ' . $exception->getMessage());
            return;
        }
        $notificationIds = array_column($batch, 'notification_id');
        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'x-api-key-node' => $this->accessToken,
                ])
                ->post($this->nodeSendNotificationUrl, [
                    'common_payload' => [
                        'title' => $this->payload->title,
                        'description' => $this->payload->description,
                        'data' => $this->payload->data,
                        'notification_type' => $this->payload->notificationType->value,
                    ],
                    'batch' => $batch,
                ]);

            if ($response->successful()) {
                $nodeResult = $response->json();
                $isSuccessful = $nodeResult['status'] ?? false;
                if ($isSuccessful){
                    // ID bản ghi thành công/thất bại (chuỗi số)
                    $successIds = $nodeResult['success_notifications'] ?? [];
                    $errorIds = $nodeResult['error_notifications'] ?? [];
                    if (!empty($successIds)) {
                        UserNotification::query()->whereIn('id', $successIds)
                            ->update(['status' => UserNotificationStatus::SENT->value]);
                    }
                    if (!empty($errorIds)) {
                        // Cập nhật trạng thái thất bại (FAILED hoặc NO_TOKEN_PROVIDED)
                        UserNotification::query()->whereIn('id', $errorIds)
                            ->update(['status' => UserNotificationStatus::FAILED->value]);
                    }
                    Log::info('Gưi thông báo thành công.', [
                        'success_count' => count($successIds),
                        'error_count' => count($errorIds),
                        'total_sent' => count($successIds) + count($errorIds),
                    ]);
                }else{
                    Log::error('SendNotifications: Node.js service returned error.', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    // Nếu mà lỗi thì phải error luôn các notification
                    UserNotification::query()->whereIn('id', $notificationIds)->update(['status' => UserNotificationStatus::FAILED->value]);
                }
            }else {
                Log::error('SendNotifications: Node.js service returned error.', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                // Nếu mà lỗi thì phải error luôn các notification
                UserNotification::query()->whereIn('id', $notificationIds)->update(['status' => UserNotificationStatus::FAILED->value]);
            }
        } catch (\Exception $exception) {
            Log::critical('SendNotifications: Connection or Timeout error.', ['error' => $exception->getMessage()]);
            // Nếu mà lỗi thì phải error luôn các notification
            UserNotification::query()->whereIn('id', $notificationIds)->update(['status' => UserNotificationStatus::FAILED->value]);
        }
    }
}
