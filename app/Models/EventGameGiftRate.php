<?php

namespace App\Models;

use App\Utils\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventGameGiftRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_game_id',
        'user_id',
        'event_game_gift_id',
        'rate',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
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
     * Get the game that owns this rate configuration
     */
    public function eventGame(): BelongsTo
    {
        return $this->belongsTo(EventGame::class);
    }

    /**
     * Get the user this rate is configured for (null = default for all)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the gift this rate applies to
     */
    public function gift(): BelongsTo
    {
        return $this->belongsTo(EventGameGift::class, 'event_game_gift_id');
    }
}
