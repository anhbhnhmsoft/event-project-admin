<?php

namespace App\Utils\Constants;

enum RoleUser: int
{
    case ADMIN = 15;
    case CUSTOMER = 25;
    case SPEAKER = 35;

    public static function getOptions(): array
    {
        return [
            self::ADMIN->value => self::ADMIN->label(),
            self::CUSTOMER->value => self::CUSTOMER->label(),
            self::SPEAKER->value => self::SPEAKER->label(),
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Quản trị viên',
            self::CUSTOMER => 'Khách hàng',
            self::SPEAKER => 'Người dẫn chương trình',
        };
    }
}
