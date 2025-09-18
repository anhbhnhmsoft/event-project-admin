<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utils\Helper;

class EventScheduleDocumentFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_schedule_document_id',
        'file_path',
        'file_name',
        'file_extension',
        'file_size',
        'file_type',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
    }

    public function eventScheduleDocument(): BelongsTo
    {
        return $this->belongsTo(EventScheduleDocument::class);
    }
}