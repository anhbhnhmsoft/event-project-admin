<?php

namespace App\Utils\Constants;

enum EventGameType: int
{
    case LUCKY_SPIN =  1;

    public function label(): string
    {
        return match ($this) {
            self::LUCKY_SPIN => 'Vòng quay may mắn',
        };
    }

    public static function getOptions(): array
    {
        return [
            self::LUCKY_SPIN->value => 'Vòng quay may mắn',
        ];
    }
}
