<?php

namespace App\Models;

use App\Utils\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventPollQuestion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_poll_id',
        'type',
        'question',
        'order',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(EventPoll::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(EventPollVote::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(EventPollQuestionOption::class, 'event_poll_question_id');
    }
}
