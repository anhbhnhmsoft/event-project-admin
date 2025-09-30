<?php

namespace App\Models;

use App\Utils\Constants\UserNotificationType;
use App\Utils\Constants\UserNotificationStatus;
use App\Utils\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
