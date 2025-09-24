<?php

namespace App\Utils\Constants;

enum TransactionType: int
{
    case MEMBERSHIP = 1;

    public static function label(int $type): string
    {
        return match ($type) {
            self::MEMBERSHIP->value => 'Mua gói thành viên',
        };
    }

    public static function typeLabel(int $type): string
    {
        return match ($type) {
            self::MEMBERSHIP->value => 'MBS',
        };
    }

    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label($case->value);
        }
        return $options;
    }
}
