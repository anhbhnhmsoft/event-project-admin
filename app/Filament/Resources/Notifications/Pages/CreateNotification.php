<?php

namespace App\Filament\Resources\Notifications\Pages;

use App\Models\User;
use App\Utils\Constants\UserNotificationStatus;
use App\Filament\Resources\Notifications\NotificationResource;
use App\Services\NotificationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
        $mode = $data['mode'] ?? 'single';

        $organizerId = $data['organizer_id'] ?? (Auth::user()->organizer_id ?? null);

        $eventId = $data['event_id'];
        $notificationType = $data['notification_type'];
        $title = $data['title'];
        $description = $data['description'];
        $customData = $data['data'] ?? [];

        $lastNotification = null;

        if ($mode === 'broadcast') {
            $userIds = User::query()
                ->when($organizerId, fn ($q) => $q->where('organizer_id', $organizerId))
                ->pluck('id')
                ->all();

            foreach ($userIds as $uid) {
                $result = $this->notificationService->createAndSendNotification(
                    $uid,
                    $organizerId,
                    $eventId,
                    $title,
                    $description,
                    $notificationType,
                    $customData
                );
                $lastNotification = $result['data']['notification'] ?? $lastNotification;
            }
        } else {
            $userIds = (array) ($data['user_ids'] ?? []);

            foreach ($userIds as $uid) {
                $result = $this->notificationService->createAndSendNotification(
                    $uid,
                    $organizerId,
                    $eventId,
                    $title,
                    $description,
                    $notificationType,
                    $customData
                );
                $lastNotification = $result['data']['notification'] ?? $lastNotification;
            }
        }

        return $lastNotification;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}


