<?php

namespace App\Utils\Constants;

enum RoleUser: int
{
    case SUPER_ADMIN = 10;
    case ADMIN = 15;
    case CUSTOMER = 25;
    case SPEAKER = 35;

    public static function getOptions(): array
    {
        return [
            self::SUPER_ADMIN->value => self::SUPER_ADMIN->label(),
            self::ADMIN->value => self::ADMIN->label(),
            self::CUSTOMER->value => self::CUSTOMER->label(),
            self::SPEAKER->value => self::SPEAKER->label(),
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => __('constants.role_user.super_admin'),
            self::ADMIN => __('constants.role_user.admin'),
            self::CUSTOMER => __('constants.role_user.customer'),
            self::SPEAKER => __('constants.role_user.speaker'),
        };
    }

    public static function checkCanAccessAdminPanel($role): bool
    {
        return in_array($role, [
            self::SUPER_ADMIN->value,
            self::ADMIN->value,
            self::SPEAKER->value
        ]);
    }

    public static function getLabel(string $value): string
    {
        return match ($value) {
            self::SUPER_ADMIN->value => __('constants.role_user.super_admin'),
            self::ADMIN->value       => __('constants.role_user.admin'),
            self::SPEAKER->value     => __('constants.role_user.speaker'),
            self::CUSTOMER->value    => __('constants.role_user.customer'),
            default                  => __('constants.role_user.unknown'),
        };
    }
}
