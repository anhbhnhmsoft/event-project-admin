<?php

namespace App\Utils\Constants;

enum Language: string
{
    case VI = 'vi';
    case EN = 'en';

    public static function getOptions(): array
    {
        return [
            self::VI->value => __('constants.language.vi'),
            self::EN->value => __('constants.language.en'),
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::VI => __('constants.language.vi'),
            self::EN => __('constants.language.en'),
        };
    }
}
