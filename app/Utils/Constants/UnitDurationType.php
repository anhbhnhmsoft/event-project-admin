<?php

namespace App\Utils\Constants;


enum UnitDurationType: int
{
    case MINUTE = 1;
    case HOUR   = 2;
    case DAY    = 3;

    public static function label(int $type): string
    {
        return match ($type) {
            self::MINUTE->value => 'Phút',
            self::HOUR->value   => 'Giờ',
            self::DAY->value    => 'Ngày',
        };
    }

    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label($case->value);
        }
        return $options;
    }
}
