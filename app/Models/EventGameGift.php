<?php

namespace App\Models;

use App\Utils\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventGameGift extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_game_id',
        'name',
        'description',
        'image',
        'quantity',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
    }

    public function eventGame(): BelongsTo
    {
        return $this->belongsTo(EventGame::class);
    }
}
