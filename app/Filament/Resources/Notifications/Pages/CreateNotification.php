<?php

namespace App\Filament\Resources\Notifications\Pages;

use App\Models\User;
use App\Utils\Constants\UserNotificationStatus;
use App\Filament\Resources\Notifications\NotificationResource;
use App\Filament\Traits\CheckPlanBeforeAccess;
use App\Jobs\SendNotifications;
use App\Models\UserNotification;
use App\Services\NotificationService;
use App\Utils\Constants\TypeSendNotification;
use App\Utils\Constants\UserNotificationType;
use App\Utils\DTO\NotificationPayload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateNotification extends CreateRecord
{
    use CheckPlanBeforeAccess;
    protected static string $resource = NotificationResource::class;
    public function getTitle(): string
    {
        return __('admin.notifications.pages.create_title');
    }

    protected NotificationService $notificationService;

    public function boot(NotificationService $notificationService): void
    {
        $this->notificationService = $notificationService;
    }

    public function mount(): void
    {
        parent::mount();
        $this->ensurePlanAccessible();
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => __('admin.notifications.pages.list_title'),
            '' => __('admin.notifications.pages.create_title'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = UserNotificationStatus::SENT->value;
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $mode = $data['mode'] ?? TypeSendNotification::SOME_USERS->value;
        $organizerId = $data['organizer_id'] ?? (Auth::user()->organizer_id ?? null);

        $notificationType = UserNotificationType::tryFrom($data['notification_type'])
            ?? UserNotificationType::SYSTEM_ANNOUNCEMENT;

        $title = $data['title'];
        $description = $data['description'];
        $customData = $data['data'] ?? [];

        $successMessage = __('admin.notifications.pages.create_success');
        $errorMessage =__('admin.notifications.pages.create_error');

        try {
            if ($mode == TypeSendNotification::ALL_USERS->value) {
                $userIds = User::query()
                    ->when($organizerId, fn($q) => $q->where('organizer_id', $organizerId))
                    ->pluck('id')
                    ->toArray();
            } else {
                $userIds = $data['user_ids'] ?? [];
            }

            // Chuẩn bị payload
            $payload = new NotificationPayload(
                title: $title,
                description: $description,
                data: $customData,
                notificationType: $notificationType,
            );

            // Lưu template nếu người dùng chọn
            if (!empty($data['save_as_template']) && !empty($data['template_name'])) {

                $dataSaveTemplate = [
                    'name' => $data['template_name'],
                    'title' => $title,
                    'description' => $description,
                    'notification_type' => $notificationType->value,
                    'organizer_id' => $organizerId,
                    'is_active' => true,
                ];
                $this->notificationService->saveTemplate($dataSaveTemplate);
            }
            //Chia nhỏ user để tránh quá tải
            $chunkSize = 500;
            foreach (array_chunk($userIds, $chunkSize) as $chunk) {
                SendNotifications::dispatch($payload, $chunk)->onQueue('notifications');
            }

            Notification::make()
                ->title(__("admin.notifications.pages.create_success"))
                ->body($successMessage)
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Log::error('Admin Make Notification - Gửi thông báo thất bại', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            Notification::make()
                ->title(__("admin.notifications.pages.create_error"))
                ->body($errorMessage)
                ->danger()
                ->send();
            return new UserNotification();
        }

        return new UserNotification();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return null;
    }
}
