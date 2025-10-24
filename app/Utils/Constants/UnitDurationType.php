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
            self::MINUTE->value => __('constants.unit_duration_type.minute'),
            self::HOUR->value   => __('constants.unit_duration_type.hour'),
            self::DAY->value    => __('constants.unit_duration_type.day'),
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
