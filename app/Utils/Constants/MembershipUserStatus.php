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
            self::INACTIVE => __('constants.membership_user_status.inactive'),
            self::ACTIVE => __('constants.membership_user_status.active'),
            self::EXPIRED => __('constants.membership_user_status.expired'),
        };
    }
}
