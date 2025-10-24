<?php

namespace App\Utils\Constants;

enum TransactionStatus: int
{
    case WAITING   = 1;
    case SUCCESS   = 2;
    case FAILED    = 3;

    public static function getLabel(int $value): string
    {
        return match ($value) {
            self::WAITING->value => __('constants.transaction_status.waiting'),
            self::SUCCESS->value => __('constants.transaction_status.success'),
            self::FAILED->value => __('constants.transaction_status.failed'),
            default => __('constants.transaction_status.unknown'),
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
