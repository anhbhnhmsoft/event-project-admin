<?php

namespace App\Utils\Constants;

enum EventStatus: int
{
    case ACTIVE = 1;
    case UPCOMING = 2;
    case CLOSED = 3;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Đang diễn ra',
            self::UPCOMING => 'Sắp diễn ra',
            self::CLOSED => 'Đã kết thúc',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::UPCOMING => 'warning',
            self::CLOSED => 'gray',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
