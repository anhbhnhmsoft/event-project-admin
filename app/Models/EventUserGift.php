<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @method static \Illuminate\Database\Eloquent\Builder|static filter(array $filters = []) // scope Filter query builder
 *
 */
class EventUserGift extends Model
{
    use SoftDeletes;

    protected $table = 'event_user_gift';

    protected $fillable = ['user_id', 'event_game_gift_id'];

    public function scopeFilter(Builder $query, array $filters = [])
    {
        if (!empty($filters['user_id'])){
            $query->where('user_id', $filters['user_id']);
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gift(): BelongsTo
    {
        return $this->belongsTo(EventGameGift::class, 'event_game_gift_id');
    }
}
