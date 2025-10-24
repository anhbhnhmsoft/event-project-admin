<?php

namespace App\Utils\Constants;

enum QuestionType: int
{

    case MULTIPLE   = 1;

    case OPEN_ENDED = 2;


    public static function label(int $type): string
    {
        return match ($type) {
            self::MULTIPLE->value   => __('constants.question_type.multiple'),
            self::OPEN_ENDED->value => __('constants.question_type.open_ended'),
            default => __('constants.question_type.unknown'),
        };
    }

    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = self::label($case->value);
        }
        return $options;
    }
}
