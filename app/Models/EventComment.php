<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Utils\Helper;
use Illuminate\Database\Eloquent\Builder;

class EventComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'content',
        'type'
    ];

    public function scopeFilter(Builder $query, array $filters = [])
    {
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['event_id'])) {
            $query->where('event_id', $filters['event_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);
            $query->where('content', 'like', '%' . $keyword . '%');
        }
    }

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
