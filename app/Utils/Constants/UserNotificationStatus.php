<?php

namespace App\Utils\Constants;

enum UserNotificationStatus: int
{
    case PENDING = 1; // Chờ
    case SENT = 2; // Đã gửi
    case READ = 3; // Đã đọc
    case FAILED = 4; // Gửi thất bại

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('constants.user_notification_status.pending'),
            self::SENT => __('constants.user_notification_status.sent'),
            self::READ => __('constants.user_notification_status.read'),
            self::FAILED => __('constants.user_notification_status.failed'),
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}

