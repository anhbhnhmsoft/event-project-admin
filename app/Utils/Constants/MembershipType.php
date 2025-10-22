<?php

namespace App\Utils\Constants;

enum MembershipType: int
{
    case FOR_CUSTOMER  = 1;
    case FOR_ORGANIZER = 2;

    public static function label(int $type): string
    {
        return match ($type) {
            self::FOR_CUSTOMER->value   => 'Gói dùng cho tổ chức',
            self::FOR_ORGANIZER->value  => 'Gói dùng cho người dùng tham gia',
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
