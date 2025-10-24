<?php

namespace App\Utils\Constants;

enum EventUserRole: int
{
    case ORGANIZER = 1;
    case PRESENTER = 2;

    public static function options(): array
    {
        return [
            self::ORGANIZER->value => __('constants.event_user_role.organizer'),
            self::PRESENTER->value => __('constants.event_user_role.presenter'),
        ];
    }
}


