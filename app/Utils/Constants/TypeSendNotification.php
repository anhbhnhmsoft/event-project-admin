<?php

namespace App\Utils\Constants;

enum TypeSendNotification: int
{
    case SOME_USERS = 1;
    case ALL_USERS  = 2;

    public static function label(int $type): string
    {
        return match ($type) {
            self::SOME_USERS->value => 'Chọn người dùng',
            self::ALL_USERS->value  => 'Broadcast (toàn bộ người dùng)',
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
