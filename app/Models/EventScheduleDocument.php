<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utils\Helper;

class EventScheduleDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_schedule_id',
        'title',
        'description',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
    }
    
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(EventSchedule::class, 'event_schedule_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(EventScheduleDocumentFile::class);
    }
}
