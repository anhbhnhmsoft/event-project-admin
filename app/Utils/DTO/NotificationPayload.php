<?php

namespace App\Utils\DTO;

use App\Utils\Constants\UserNotificationType;

final readonly class NotificationPayload
{
    public function __construct(
        public string               $title,
        public string               $description,
        public array                $data,
        public UserNotificationType $notificationType
    ) {
    }
}
