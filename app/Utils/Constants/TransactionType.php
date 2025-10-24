<?php

namespace App\Utils\Constants;

use App\Utils\Helper;

enum TransactionType: int
{
    case MEMBERSHIP   = 1;
    case PLAN_SERVICE = 2;
    case BUY_DOCUMENT = 4;
    case BUY_COMMENT =  5;
    case EVENT_SEAT   = 3;

    public static function label(int $type): string
    {
        return match ($type) {
            self::MEMBERSHIP->value   => __('constants.transaction_type.membership'),
            self::PLAN_SERVICE->value => __('constants.transaction_type.plan_service'),
            self::BUY_DOCUMENT->value => __('constants.transaction_type.buy_document'),
            self::BUY_COMMENT->value  => __('constants.transaction_type.buy_comment'),
            self::EVENT_SEAT->value   => __('constants.transaction_type.event_seat'),
        };
    }

    public function getDescTrans(): string
    {
        return match ($this) {
            self::MEMBERSHIP   => 'MBS' . Helper::getTimestampAsId(),
            self::PLAN_SERVICE => 'PLS' . Helper::getTimestampAsId(),
            self::BUY_DOCUMENT => 'BDM' . Helper::getTimestampAsId(),
            self::BUY_COMMENT  => 'BCM' . Helper::getTimestampAsId(),
            self::EVENT_SEAT   => 'EVT' . Helper::getTimestampAsId(),
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
