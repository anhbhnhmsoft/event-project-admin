<?php

namespace App\Models;

use App\Utils\Helper;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|static filter(array $filters = []) // scope Filter query builder
 * @method static \Illuminate\Database\Eloquent\Builder|static sortBy(string $sortBy = '') // scope SortBy query builder
 *
 */
class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organizer_id',
        'name',
        'short_description',
        'description',
        'day_represent',
        'start_time',
        'end_time',
        'image_represent_path',
        'status',
        'address',
        'province_code',
        'district_code',
        'ward_code',
        'latitude',
        'longitude',
        'free_to_join',
    ];
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'status' => 'integer',
        'free_to_join' => 'boolean',
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
        if (!empty($filters['lat']) && !empty($filters['lng'])) {
            $lat = (float)$filters['lat'];
            $lng = (float)$filters['lng'];
            $query->whereNotNull('latitude')->whereNotNull('longitude');
            $query->selectRaw(
                'events.*, (6371 * acos( cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)) )) as distance_km',
                [$lat, $lng, $lat]
            );
        }

        if (!empty($filters['exclude_id'])) {
            $query->whereNot('id', $filters['exclude_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['start_time'])) {
            $start = \Carbon\Carbon::parse($filters['start_time'])->startOfDay();
            $query->whereDate('start_time', '>=', $start);
        }

        if (!empty($filters['end_time'])) {
            $end = \Carbon\Carbon::parse($filters['end_time'])->endOfDay();
            $query->whereDate('end_time', '<=', $end);
        }

        if (!empty($filters['province_code'])) {
            $query->where('province_code', $filters['province_code']);
        }

        if (!empty($filters['district_code'])) {
            $query->where('district_code', $filters['district_code']);
        }

        if (!empty($filters['ward_code'])) {
            $query->where('ward_code', $filters['ward_code']);
        }

        if (!empty($filters['organizer_id'])) {
            $query->where('organizer_id', $filters['organizer_id']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);
            $query->where('name', 'like', '%' . $keyword . '%')->orWhere('address', 'like', '%' . $keyword . '%');;
        }

        if (!empty($filters['user_id'])) {
            if (!empty($filters['event_history_status'])) {
                $query->whereHas('eventUserHistories', function (Builder $query) use ($filters) {
                    $query->where('user_id', $filters['user_id'])
                        ->where('status', $filters['event_history_status']);
                });
            }
            if (!empty($filters['event_history_statuses'])) {
                $query->whereHas('eventUserHistories', function (Builder $query) use ($filters) {
                    $query->where('user_id', $filters['user_id'])
                        ->whereIn('status', $filters['event_history_statuses']);
                });
            }
        }
    }

    public function scopeSortBy(Builder $query, string $sortBy = '')
    {
        switch ($sortBy) {
            case 'distance_asc':
                if (Helper::checkColumnSelected($query, 'distance_km')) {
                    $query->orderBy('distance_km', 'asc');
                }
                break;
            case 'distance_desc':
                if (Helper::checkColumnSelected($query, 'distance_km')) {
                    $query->orderBy('distance_km', 'desc');
                }
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }


    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_code', 'code');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_code', 'code');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(EventSchedule::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(EventUser::class, 'event_id');
    }

    public function areas(): HasMany
    {
        return $this->hasMany(EventArea::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(EventComment::class);
    }

    public function eventUserHistories(): HasMany
    {
        return $this->hasMany(EventUserHistory::class);
    }

    public function games(): HasMany
    {
        return $this->hasMany(EventGame::class);
    }

    public function usersHasTicket(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, EventUserHistory::class, 'event_id', 'id', 'id', 'user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user_histories', 'event_id', 'user_id')
            ->using(EventUserHistory::class)
            ->withPivot(['status', 'ticket_code', 'event_seat_id', 'id'])
            ->withTimestamps();
    }
}
