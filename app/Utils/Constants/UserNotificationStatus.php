<?php

namespace App\Utils\Constants;

enum UserNotificationStatus: int
{
    case SENT = 1; // Đã gửi
    case READ = 2; // Đã đọc
    case FAILED = 3; // Gửi thất bại

    public function label(): string
    {
        return match ($this) {
            self::SENT => 'Đã gửi',
            self::READ => 'Đã đọc',
            self::FAILED => 'Gửi thất bại',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}

