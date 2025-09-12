<?php

namespace App\Utils;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;

final class Helper
{
    public static function getTimestampAsId(): int
    {
        // Get microtime float
        $microFloat = microtime(true);
        $microTime = Carbon::createFromTimestamp($microFloat);
        $formatted = $microTime->format('ymdHisu');
        usleep(100);
        return (int)$formatted;
    }

    public static function generateURLImagePath(?string $filePath): ?string
    {
        if (!empty($filePath)) {
            return route('public_image', ['file_path' => $filePath]);
        }
        return null;
    }

    public static function generateUiAvatarUrl(?string $name, ?string $email): string
    {
        $text = $name ?: ($email ?: 'User');
        return 'https://ui-avatars.com/api/?name=' . urlencode($text) . '&background=random&color=random';
    }
}
