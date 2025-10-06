<?php

namespace App\Models;

use App\Utils\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventScheduleDocumentUser extends Model
{
    use SoftDeletes;

    protected $table = 'event_schedule_document_user';

    protected $fillable = [
        'user_id',
        'event_schedule_document_id'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(EventScheduleDocumentFile::class);
    }
}
