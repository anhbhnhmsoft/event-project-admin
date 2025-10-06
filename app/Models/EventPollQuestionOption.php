<?php

namespace App\Models;

use App\Utils\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventPollQuestionOption extends Model
{
    
    use SoftDeletes;

    protected $fillable = [
        'event_poll_question_id',
        'label',
        'order',
        'is_correct',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(EventPollQuestion::class);
    }

    public function votes() : BelongsTo 
    {
        return $this->belongsTo(EventPollVote::class);
    }
}
