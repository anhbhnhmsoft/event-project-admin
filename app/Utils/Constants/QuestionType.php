<?php

namespace App\Utils\Constants;

enum QuestionType: int
{
    case SINGLE = 1;

    public static function label(int $type): string
    {
        return match ($type) {
            self::SINGLE->value => 'Single choice (Một đáp án)',
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
