<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Utils\Helper;

class EventUserHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'event_seat_id',
        'ticket_code',
        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
            
            if (empty($model->ticket_code)) {
                do {
                    $ticketCode = 'TICKET-' . Helper::getTimestampAsId();
                } while (self::where('ticket_code', $ticketCode)->exists());
                
                $model->ticket_code = $ticketCode;
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
