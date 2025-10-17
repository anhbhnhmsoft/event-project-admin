<?php

namespace App\Utils;

use App\Utils\Constants\RoleUser;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;

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
            $filePath = str_replace('\\', '/', $filePath);
            return route('public_image', ['file_path' => $filePath]);
        }
        return null;
    }

    public static function generateUiAvatarUrl(?string $name, ?string $email): string
    {
        $text = $name ?: ($email ?: 'User');
        return 'https://ui-avatars.com/api/?name=' . urlencode($text) . '&background=random&color=random';
    }

    public static function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public static function checkColumnSelected(Builder $query, string $name): bool
    {
        $cols = collect($query->getQuery()->columns);

        return $cols->contains(function ($col) use ($name) {
            return is_string($col) && stripos($col, $name) !== false;
        });
    }

    //Chuyển thời gian HH:MM sang tổng số phút.
    public static function timeToMinutes(?string $time): ?int
    {
        if (empty($time)) {
            return null;
        }

        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
            return null;
        }

        [$hour, $minute] = explode(':', $time);
        return ((int) $hour) * 60 + ((int) $minute);
    }

    //Tạo URL đăng ký nhanh cho event với token mã hóa.
    public static function quickRegisterUrl($event): string
    {
        $payload = [
            'event_id' => $event->id,
            'organizer_id' => $event->organizer_id,
        ];

        $token = Crypt::encryptString(json_encode($payload));


        return route('events.quick-register', ['token' => $token]);
    }

    public static function generateQRCodeBanking($binBank, $bankNumber, $bankName, $amount, $addInfo = null): string
    {

        $url = "https://img.vietqr.io/image/{$binBank}-{$bankNumber}-print.jpg?amount={$amount}&accountName={$bankName}";
        if ($addInfo) {
            $url .= "&addInfo=" . urlencode($addInfo);
        }
        return $url;
    }

    public static function generateSignature(array $data, string $key): string
    {
        ksort($data);

        $dataString = urldecode(http_build_query($data));

        return hash_hmac('sha256', $dataString, $key);
    }

    public static function checkSuperAdmin(): bool
    {
        if (auth()->check()) {
            $user = auth()->user();
            return $user->role === RoleUser::SUPER_ADMIN->value;
        }
        return false;
    }

    public static function checkAdmin(): bool
    {
        if (auth()->check()) {
            $user = auth()->user();
            return in_array($user->role, [RoleUser::SUPER_ADMIN->value, RoleUser::ADMIN->value]);
        }
        return false;
    }
}
