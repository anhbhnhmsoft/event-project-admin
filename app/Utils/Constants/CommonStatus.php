<?php

namespace App\Utils\Constants;

enum CommonStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 0;

    public static function getOptions(): array
    {
        return [
            self::ACTIVE->value => 'Hoạt động',
            self::INACTIVE->value => 'Không hoạt động',
        ];
    }

        public function getLabel(CommonStatus $state): array
    {
            return self::getOptions()[$state->value];
    }
}