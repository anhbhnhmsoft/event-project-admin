<?php

namespace App\Models;

use App\Utils\Constants\CommonStatus;
use App\Utils\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'status' => 'integer',
    ];


    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
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

    public function areas(): HasMany
    {
        return $this->hasMany(EventArea::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(EventComment::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
