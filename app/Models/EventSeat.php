<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utils\Helper;
class EventSeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_area_id',
        'seat_code',
        'status',
        'user_id'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(EventArea::class, 'event_area_id');
    }

    public function eventUserHistories(): HasMany
    {
        return $this->hasMany(EventUserHistory::class, 'event_seat_id');
    }

    public function user () : BelongsTo 
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
