<?php

namespace App\Utils\Constants;

enum ConfigMembership: string
{
    case ALLOW_COMMENT     = 'allow_comment';
    case ALLOW_CHOOSE_SEAT = 'allow_choose_seat';
    case ALLOW_DOCUMENTARY = 'allow_documentary';

    public function label(): string
    {
        return match ($this) {
            self::ALLOW_COMMENT     => 'Cho phép bình luận',
            self::ALLOW_CHOOSE_SEAT => 'Cho phép chọn chỗ ngồi',
            self::ALLOW_DOCUMENTARY => 'Cho phép xem tải hay xem tài liệu trong sự kiện',
        };
    }

    public function type(): string
    {
        return match ($this) {
            self::ALLOW_COMMENT     => 'boolean',
            self::ALLOW_CHOOSE_SEAT => 'boolean',
            self::ALLOW_DOCUMENTARY => 'boolean',
        };
    }
}
