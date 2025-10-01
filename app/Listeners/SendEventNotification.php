<?php

namespace App\Listeners;

use App\Events\EventCreated;
use App\Services\NotificationService;
use App\Utils\Constants\UserNotificationType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendEventNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected NotificationService $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(EventCreated $event): void
    {
        $eventModel = $event->event;
        
        $users = $eventModel->organizer->users;
        
        foreach ($users as $user) {
            $this->notificationService->createAndSendNotification(
                $user->id,
                $eventModel->name,
                $eventModel->short_description ?? $eventModel->description,
                UserNotificationType::EVENT_REMINDER->value,
                [
                    'event_id' => $eventModel->id,
                    'event_name' => $eventModel->name,
                    'start_time' => $eventModel->start_time,
                    'end_time' => $eventModel->end_time,
                ]
            );
        }
    }
}
