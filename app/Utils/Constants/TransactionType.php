<?php

namespace App\Utils\Constants;

use App\Utils\Helper;

enum TransactionType: int
{
    case MEMBERSHIP = 1;

    public static function label(int $type): string
    {
        return match ($type) {
            self::MEMBERSHIP->value => 'Mua gói thành viên',
        };
    }

    public function getDescTrans(): string
    {
        return match ($this) {
            self::MEMBERSHIP => 'MBS' . Helper::getTimestampAsId(),
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
