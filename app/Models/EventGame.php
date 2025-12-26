<?php

namespace App\Models;

use App\Utils\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventGame extends Model
{
    protected $table = 'event_games';

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'game_type',
        'config_game',
    ];

    protected $casts = [
        'config_game' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->beLongsTo(Event::class);
    }

    public function gifts(): HasMany
    {
        return $this->hasMany(EventGameGift::class, 'event_game_id');
    }

    public function giftRates(): HasMany
    {
        return $this->hasMany(EventGameGiftRate::class, 'event_game_id');
    }
}
