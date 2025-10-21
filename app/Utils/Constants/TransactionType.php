<?php

namespace App\Utils\Constants;

use App\Utils\Helper;

enum TransactionType: int
{
    case MEMBERSHIP   = 1;
    case PLAN_SERVICE = 2;
    case BUY_DOCUMENT = 4;
    case BUY_COMMENT =  5;

    public static function label(int $type): string
    {
        return match ($type) {
            self::MEMBERSHIP->value   => 'Mua gói thành viên',
            self::PLAN_SERVICE->value => 'Mua gói dịch vụ',
            self::BUY_DOCUMENT->value => 'Mua tài liệu sự kiện',
            self::BUY_COMMENT->value  => 'Mua quyền bình luận ',
        };
    }

    public function getDescTrans(): string
    {
        return match ($this) {
            self::MEMBERSHIP   => 'MBS' . Helper::getTimestampAsId(),
            self::PLAN_SERVICE => 'PLS' . Helper::getTimestampAsId(),
            self::BUY_DOCUMENT => 'BDM' . Helper::getTimestampAsId(),
            self::BUY_COMMENT  => 'BCM' . Helper::getTimestampAsId(),
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
