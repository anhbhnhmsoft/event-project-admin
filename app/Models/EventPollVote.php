<?php

namespace App\Models;

use App\Utils\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventPollVote extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'event_poll_question_id',
        'event_poll_question_option_id',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
    }

    public function question()
    {
        return $this->belongsTo(EventPollQuestion::class, 'event_poll_question_id');
    }


    public function option(): BelongsTo
    {
        return $this->belongsTo(EventPollQuestionOption::class, 'event_poll_question_option_id');
    }
}
