<?php

namespace App\Models;

use App\Utils\Constants\MembershipUserStatus;
use App\Utils\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organizer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'name',
        'image',
        'description',
        'status'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function plansActive()
    {
        return $this->belongsToMany(Membership::class, 'membership_organizer')
            ->withPivot(['start_date', 'end_date', 'status'])
            ->wherePivot('status', MembershipUserStatus::ACTIVE->value)
            ->withTimestamps();
    }

    public function plans()
    {
        return $this->belongsToMany(Membership::class, 'membership_organizer')
            ->withPivot(['start_date', 'end_date', 'status'])
            ->withTimestamps();
    }
}
