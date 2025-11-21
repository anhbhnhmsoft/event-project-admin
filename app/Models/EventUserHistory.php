<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Utils\Helper;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EventUserHistory extends Pivot
{
    use HasFactory;

    protected $table = 'event_user_histories';

    protected $fillable = [
        'event_id',
        'user_id',
        'event_seat_id',
        'ticket_code',
        'status',
        'features'
    ];

    protected $casts = [
        'features' => 'array'
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
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(EventSeat::class, 'event_seat_id');
    }
}
