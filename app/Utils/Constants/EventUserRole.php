<?php

namespace App\Utils\Constants;

enum EventUserRole: int
{
    case ORGANIZER = 1;
    case PRESENTER = 2;

    public static function options(): array
    {
        return [
            self::ORGANIZER->value => 'Người tổ chức',
            self::PRESENTER->value => 'Người dẫn chương trình',
        ];
    }
}


