<?php

namespace App\Utils\Constants;

enum Language: string
{
    case VI = 'vi';
    case EN = 'en';

    public static function getOptions(): array
    {
        return [
            self::VI->value => 'Tiếng Việt',
            self::EN->value => 'English',
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::VI => 'Tiếng Việt',
            self::EN => 'English',
        };
    }
}
