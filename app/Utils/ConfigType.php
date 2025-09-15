<?php

namespace App\Utils\Constants;

enum ConfigType: int
{
    case IMAGE = 1;
    case STRING = 2;

    public static function getOptions(): array
    {
        return [
            self::IMAGE->value => 'Ảnh',
            self::STRING->value => 'Chuỗi',
        ];
    }

        public function getLabel(ConfigType $state): array
    {
            return self::getOptions()[$state->value];
    }
}