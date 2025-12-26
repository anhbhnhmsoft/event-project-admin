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

    public static function toOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => self::getLabel($status->value)])
            ->toArray();
    }

    public static function getLabel(int $value): string
    {
        return self::tryFrom($value)?->label() ;
    }
}
