<?php

namespace App\Utils\Constants;

enum TransactionStatus: int
{
    case WAITING = 1;
    case SUCCESS = 2;
    case FAILED = 3;

    public static function getLabel(int $value): string
    {
        return match ($value) {
            self::WAITING->value => 'Đang chờ xử lý',
            self::SUCCESS->value => 'Thành công',
            self::FAILED->value => 'Thất bại',
            default => 'Không xác định',
        };
    }


    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel($case->value);
        }
        return $options;
    }
}
