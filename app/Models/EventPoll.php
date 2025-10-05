<?php

namespace App\Models;

use App\Utils\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventPoll extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'title',
        'access_type',
        'start_time',
        'end_time',
        'duration_unit',
        'duration',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
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

    public function questions(): HasMany
    {
        return $this->hasMany(EventPollQuestion::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_poll_user', 'event_poll_id', 'user_id')
            ->withTimestamps()
            ->using(EventPollUser::class); 
    }
}
