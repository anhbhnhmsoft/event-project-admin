<?php

namespace App\Utils\Constants;

enum EventCommentType: int
{
    case PUBLIC  = 1;
    case PRIVATE = 2;

    public static function label(int $type): string
    {
        return match ($type) {
            self::PUBLIC->value   => __('constants.event_comment_type.public'),
            self::PRIVATE->value  => __('constants.event_comment_type.private'),
        };
    }
}
