<?php

namespace App\Utils\Constants;

use Illuminate\Support\Facades\Auth;

enum RoleUser: int
{
    case SUPER_ADMIN = 10;
    case ADMIN = 15;
    case SPEAKER = 25;
    case CUSTOMER = 35;

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
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Quản trị viên',
            self::CUSTOMER => 'Khách hàng',
            self::SPEAKER => 'Người dẫn chương trình',
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

    public static function canAccess($role): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        return $user->role == $role->value;
    }
}
