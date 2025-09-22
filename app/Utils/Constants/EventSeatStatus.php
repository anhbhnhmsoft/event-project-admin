<?php

namespace App\Utils\Constants;

enum EventSeatStatus: int
{
    case AVAILABLE = 1;
    case BOOKED    = 2;

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Trống',
            self::BOOKED => 'Đã đặt',
        };
    }
}
