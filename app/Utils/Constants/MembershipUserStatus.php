<?php

namespace App\Utils\Constants;

enum MembershipUserStatus: int
{
    case INACTIVE = 0;
    case ACTIVE = 1;
    case EXPIRED = 2;

    public function label(): string
    {
        return match ($this) {
            self::INACTIVE => 'Chưa kích hoạt',
            self::ACTIVE => 'Đang hoạt động',
            self::EXPIRED => 'Hết hạn',
        };
    }
}
