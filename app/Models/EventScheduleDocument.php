<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utils\Helper;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|static filter(array $filters = []) // scope Filter query builder
 * @method static \Illuminate\Database\Eloquent\Builder|static sortBy(string $sortBy = '') // scope SortBy query builder
 *
 */
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

    public function scopeFilter(Builder $query, array $filters = [])
    {
        if (!empty($filters['user_id'])) {
            $query->whereHas('users', function (Builder $q) use ($filters) {
                $q->where('user_id', $filters['user_id']);
            });
        }
    }

    public function scopeSortBy(Builder $query, string $sortBy = '')
    {
        switch ($sortBy) {
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }

    public function eventSchedule(): BelongsTo
    {
        return $this->belongsTo(EventSchedule::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(EventScheduleDocumentFile::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'event_schedule_document_user')
            ->withTimestamps()
            ->withPivot('id')
            ->orderByPivot('created_at', 'desc')
            ->using(EventScheduleDocumentUser::class);
    }
}
