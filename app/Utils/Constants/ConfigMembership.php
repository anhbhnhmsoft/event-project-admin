<?php

namespace App\Utils\Constants;

enum ConfigMembership: string
{
    case ALLOW_COMMENT     = 'allow_comment';
    case ALLOW_CHOOSE_SEAT = 'allow_choose_seat';
    case ALLOW_DOCUMENTARY = 'allow_documentary';

    case LIMIT_EVENT       = 'limit_event';
    case LIMIT_MEMBER      = 'limit_member';
    case FEATURE_POLL      = 'feature_poll';
    case FEATURE_GAME      = 'feature_game';
    case FEATURE_COMMENT   = 'feature_comment';


    public function labelAdmin(): string
    {
        return match ($this) {
            self::ALLOW_COMMENT     => 'Cho phép bình luận',
            self::ALLOW_CHOOSE_SEAT => 'Cho phép chọn chỗ ngồi',
            self::ALLOW_DOCUMENTARY => 'Cho phép xem tải hay xem tài liệu trong sự kiện',
        };
    }

    public function typeAdmin(): string
    {
        return match ($this) {
            self::ALLOW_COMMENT     => 'boolean',
            self::ALLOW_CHOOSE_SEAT => 'boolean',
            self::ALLOW_DOCUMENTARY => 'boolean',
        };
    }


    public function labelSuperAdmin(): string
    {
        return match ($this) {
            self::LIMIT_EVENT     => 'Giới hạn số sự kiện',
            self::LIMIT_MEMBER    => 'Giới hạn thành viên tham gia sự kiện',
            self::FEATURE_POLL    => 'Tính năng nhận xét/ khảo sát sau hoặc trước sự kiện',
            self::FEATURE_GAME    => 'Tính năng trò chơi trong sự kiện',
            self::FEATURE_COMMENT => 'Tính năng bình luận trong sự kiện',
        };
    }

    public function typeSuperAdmin(): string
    {
        return match ($this) {
            self::LIMIT_EVENT     => 'number',
            self::LIMIT_MEMBER    => 'number',
            self::FEATURE_POLL    => 'boolean',
            self::FEATURE_GAME    => 'boolean',
            self::FEATURE_COMMENT => 'boolean',
        };
    }
}
