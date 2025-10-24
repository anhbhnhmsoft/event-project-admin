<?php

namespace App\Utils\Constants;

enum MembershipType: int
{
    case FOR_CUSTOMER  = 1;
    case FOR_ORGANIZER = 2;

    public static function label(int $type): string
    {
        return match ($type) {
            self::FOR_CUSTOMER->value   => __('constants.membership_type.for_customer'),
            self::FOR_ORGANIZER->value  => __('constants.membership_type.for_organizer'),
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
