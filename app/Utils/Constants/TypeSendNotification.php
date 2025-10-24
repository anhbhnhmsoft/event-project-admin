<?php

namespace App\Utils\Constants;

enum TypeSendNotification: int
{
    case SOME_USERS = 1;
    case ALL_USERS  = 2;

    public static function label(int $type): string
    {
        return match ($type) {
            self::SOME_USERS->value => __('constants.type_send_notification.some_users'),
            self::ALL_USERS->value  => __('constants.type_send_notification.all_users'),
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
