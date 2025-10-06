<?php

namespace App\Models;

use App\Utils\Constants\UserNotificationType;
use App\Utils\Constants\UserNotificationStatus;
use App\Utils\Helper;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|static filter(array $filters = []) // scope Filter query builder
 * @method static \Illuminate\Database\Eloquent\Builder|static sortBy(string $sortBy = '') // scope SortBy query builder
 *
 */
class UserNotification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'data',
        'notification_type',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
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

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['notification_type'])) {
            $query->where('notification_type', $filters['notification_type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['statuses'])) {
            $query->whereIn('status', $filters['statuses']);
        }
    }

    public function scopeSortBy(Builder $query, string $sortBy = '')
    {
        switch ($sortBy) {
            case 'created_at':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getNotificationTypeLabelAttribute(): string
    {
        return UserNotificationType::from($this->notification_type)->label();
    }

    public function getNotificationStatusLabelAttribute(): string
    {
        return UserNotificationStatus::from($this->status)->label();
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
