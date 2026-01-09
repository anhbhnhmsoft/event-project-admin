<?php

namespace App\Utils\Constants;

enum TransactionTypePayment: int
{
    case CASSO = 1;
    case IAP = 2; // In-App Purchase (RevenueCat)

    public function label(): string
    {
        return match ($this) {
            self::CASSO => 'Bank Transfer',
            self::IAP => 'In-App Purchase',
        };
    }
}
