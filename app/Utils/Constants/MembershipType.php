<?php

namespace App\Utils\Constants;

enum MembershipType: int
{
    case FOR_CUSTOMER  = 1;
    case FOR_ORGANIZER = 2;

    public static function label(int $type): string
    {
        return match ($type) {
            self::FOR_CUSTOMER->value   => 'Dùng cho khách hàng sự kiện',
            self::FOR_ORGANIZER->value  => 'Dùng cho khách hàng hệ thống',
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
