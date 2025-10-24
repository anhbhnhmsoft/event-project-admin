<?php

namespace App\Utils\Constants;

enum CommonStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 0;

    public static function getOptions(): array
    {
        return [
            self::ACTIVE->value => __('constants.common_status.active'),
            self::INACTIVE->value => __('constants.common_status.inactive'),
        ];
    }

        public function getLabel(CommonStatus $state): array
    {
            return self::getOptions()[$state->value];
    }
}