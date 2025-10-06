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
            self::PENDING => 'Chờ gửi',
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

