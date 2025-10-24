<?php

namespace App\Utils\Constants;

enum EventGameType: int
{
    case LUCKY_SPIN =  1;

    public function label(): string
    {
        return match ($this) {
            self::LUCKY_SPIN => __('constants.event_game_type.lucky_spin'),
        };
    }

    public static function getOptions(): array
    {
        return [
            self::LUCKY_SPIN->value => __('constants.event_game_type.lucky_spin'),
        ];
    }
}
