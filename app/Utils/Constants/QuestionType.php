<?php

namespace App\Utils\Constants;

enum QuestionType: int
{

    case MULTIPLE   = 1;

    case OPEN_ENDED = 2;


    public static function label(int $type): string
    {
        return match ($type) {
            self::MULTIPLE->value   => 'Choice (Nhiều lựa chọn)',
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
