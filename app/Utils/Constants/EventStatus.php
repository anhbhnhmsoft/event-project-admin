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
            self::ACTIVE => __('constants.event_status.active'),
            self::UPCOMING => __('constants.event_status.upcoming'),
            self::CLOSED => __('constants.event_status.closed'),
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
