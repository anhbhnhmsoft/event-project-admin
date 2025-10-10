<?php

namespace App\Filament\Resources\Notifications\Pages;

use App\Models\User;
use App\Utils\Constants\UserNotificationStatus;
use App\Filament\Resources\Notifications\NotificationResource;
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

class CreateNotification extends CreateRecord
{
    protected static string $resource = NotificationResource::class;
    protected static ?string $title = 'Tạo thông báo';

    protected NotificationService $notificationService;

    public function boot(NotificationService $notificationService): void
    {
        $this->notificationService = $notificationService;
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Thông báo',
            '' => 'Tạo thông báo',
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

        $successMessage = 'Thông báo đã được đưa vào hàng đợi để gửi thành công.';
        $errorMessage = 'Lỗi! Không thể đưa thông báo vào hàng đợi.';
        $userIds = [];

        if ($mode == TypeSendNotification::ALL_USERS->value) {
            $userIds = User::query()
                ->when($organizerId, fn($q) => $q->where('organizer_id', $organizerId))
                ->pluck('id')->toArray();
            $successMessage = 'Thông báo Broadcast đã được đưa vào hàng đợi để gửi thành công.';
        }
        try {
            $payload = new NotificationPayload(
                title: $title,
                description: $description,
                data: $customData,
                notificationType: $notificationType,
            );

            SendNotifications::dispatch($payload, $userIds)->onQueue('notifications');

            Notification::make()
                ->title('Thành công')
                ->body($successMessage)
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Log::error('Admin Make Notification - Gửi thông báo thất bại', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            Notification::make()
                ->title('Lỗi')
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
