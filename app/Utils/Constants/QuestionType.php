<?php

namespace App\Utils\Constants;

enum QuestionType: int
{
    case SINGLE = 1;

    case MULTIPLE = 2;

    case OPEN_ENDED = 3;


    public static function label(int $type): string
    {
        return match ($type) {
            self::SINGLE->value => 'Single choice (Một đáp án)',
            self::MULTIPLE->value => 'Multi-choice (Nhiều đáp án)',
            self::OPEN_ENDED->value => 'Text Answer (Trả lời tự do)',
            default => 'Không xác định',
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
