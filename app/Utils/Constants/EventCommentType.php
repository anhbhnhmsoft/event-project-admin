<?php

namespace App\Utils\Constants;

enum EventCommentType: int
{
    case PUBLIC  = 1;
    case PRIVATE = 2;

    public static function label(int $type): string
    {
        return match ($type) {
            self::PUBLIC->value   => 'Bình luận chung',
            self::PRIVATE->value  => 'Bình luận riêng ',
        };
    }
}
