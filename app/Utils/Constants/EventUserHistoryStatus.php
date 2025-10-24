<?php

namespace App\Utils\Constants;

enum EventUserHistoryStatus: int
{
    case SEENED = 1;
    case BOOKED = 2;
    case PARTICIPATED = 3;
    case CANCELLED = 4;

    public function label(): string
    {
        return match ($this) {
            self::SEENED => 'Đã xem ~ Chưa thanh toán',
            self::BOOKED => 'Đã đặt vé',
            self::PARTICIPATED => 'Đã tham gia',
            self::CANCELLED => 'Đã hủy',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function getLabel($case) : string
    {
        return match ($case) {
            self::SEENED->value => 'Đã xem',
            self::BOOKED->value => 'Đã đặt vé',
            self::PARTICIPATED->value => 'Đã tham gia',
            self::CANCELLED->value => 'Đã hủy',
        };
    }
}
