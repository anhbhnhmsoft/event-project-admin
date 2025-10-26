<?php

namespace App\Utils\Constants;

enum EventUserHistoryStatus: int
{
    case SEENED = 1;
    case BOOKED = 2;
    case PARTICIPATED = 3;
    case CANCELLED = 4;

    public function label(): string
    {
        return match ($this) {
            self::SEENED => __('constants.event_user_history_status.seened'),
            self::BOOKED => __('constants.event_user_history_status.booked'),
            self::PARTICIPATED => __('constants.event_user_history_status.participated'),
            self::CANCELLED => __('constants.event_user_history_status.cancelled'),
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function getLabel($case) : string
    {
        return match ($case) {
            self::SEENED->value => __('constants.event_user_history_status.seened_short'),
            self::BOOKED->value => __('constants.event_user_history_status.booked'),
            self::PARTICIPATED->value => __('constants.event_user_history_status.participated'),
            self::CANCELLED->value => __('constants.event_user_history_status.cancelled'),
        };
    }
}
