<?php

namespace App\Models;

use App\Utils\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZaloToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'access_token',
        'refresh_token',
        'expired_at',
        'organizer_id',
    ];

    protected $casts = [
        'expired_at' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
    }

    /**
     * Get the latest token record
     * 
     * @return ZaloToken|null
     */
    /**
     * Get the latest token record for a specific organizer
     * 
     * @param int|null $organizerId
     * @return ZaloToken|null
     */
    public static function getLatest($organizerId = 1): ?ZaloToken
    {
        return static::where('organizer_id', $organizerId)->latest('id')->first();
    }

    /**
     * Check if access token is expired
     * 
     * @return bool
     */
    public function isAccessTokenExpired(): bool
    {
        return $this->expired_at <= time();
    }

    /**
     * Check if access token will expire soon (within 5 minutes)
     * 
     * @return bool
     */
    public function willExpireSoon(): bool
    {
        return $this->expired_at <= (time() + 300); // 5 minutes
    }

    /**
     * Update tokens
     * 
     * @param string $accessToken
     * @param string|null $refreshToken
     * @param int $expiresIn
     * @return void
     */
    public function updateTokens(string $accessToken, ?string $refreshToken = null, int $expiresIn = 3600): void
    {
        $this->access_token = $accessToken;

        if ($refreshToken) {
            $this->refresh_token = $refreshToken;
        }

        $this->expired_at = time() + $expiresIn;
        $this->save();
    }

    /**
     * Create or update token record
     * 
     * @param string $accessToken
     * @param string $refreshToken
     * @param int $expiresIn
     * @param int $organizerId
     * @return ZaloToken
     */
    public static function createOrUpdate(string $accessToken, string $refreshToken, int $expiresIn = 3600, int $organizerId = 1): ZaloToken
    {
        $token = static::getLatest($organizerId);

        if ($token) {
            $token->updateTokens($accessToken, $refreshToken, $expiresIn);
            return $token;
        }

        return static::create([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expired_at' => time() + $expiresIn,
            'organizer_id' => $organizerId,
        ]);
    }
}
