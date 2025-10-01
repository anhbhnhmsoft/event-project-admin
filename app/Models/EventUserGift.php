<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventUserGift extends Model
{
    use SoftDeletes;
    
    protected $table = 'event_user_gift';

    protected $fillable = ['user_id', 'event_game_gift_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gift(): BelongsTo
    {
        return $this->belongsTo(EventGameGift::class, 'event_game_gift_id');
    }
}
