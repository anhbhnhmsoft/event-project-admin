<?php

namespace App\Utils\Constants;

enum EventSeatStatus: int
{
    case AVAILABLE = 1;
    case RESERVED  = 2;
    case BOOKED    = 3;

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Trống',
            self::RESERVED => 'Chờ',
            self::BOOKED => 'Đã đặt',
        };
    }
}
