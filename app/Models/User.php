<?php

namespace App\Models;

use App\Utils\Constants\CommonStatus;
use App\Utils\Constants\MembershipUserStatus;
use App\Utils\Constants\RoleUser;
use App\Utils\Helper;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    use HasFactory, Notifiable;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'phone',
        'address',
        'gender',
        'introduce',
        'role',
        'avatar_path',
        'email_verified_at',
        'phone_verified_at',
        'organizer_id',
        'lang',
        'inactive'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'inactive' => 'boolean'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
    }
    public function scopeWhereHasActiveOrganizer(Builder $query, int $organizerId): void
    {
        $query->whereHas('organizer', function ($q) use ($organizerId) {
            $q->where('organizer_id', $organizerId)
                ->where('status', CommonStatus::ACTIVE->value);
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return RoleUser::checkCanAccessAdminPanel($this->role);
    }

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    public function memberships()
    {
        return $this->belongsToMany(Membership::class, 'membership_user')
            ->withPivot(['start_date', 'end_date', 'status'])
            ->withTimestamps();
    }

    public function activeMemberships()
    {
        return $this->belongsToMany(Membership::class, 'membership_user')
            ->withPivot(['start_date', 'end_date', 'status'])
            ->wherePivot('status', MembershipUserStatus::ACTIVE->value);
    }

    public function activeMembership()
    {
        return $this->belongsToMany(Membership::class, 'membership_user')
            ->withPivot(['start_date', 'end_date', 'status'])
            ->wherePivot('status', MembershipUserStatus::ACTIVE->value)->limit(1);
    }

    public function membershipsUser ()
    {
        return $this->hasMany(MembershipUser::class);
    }

    public function eventUserHistories(): HasMany
    {
        return $this->hasMany(EventUserHistory::class);
    }

    public function eventScheduleDocuments()
    {
        return $this->belongsToMany(EventScheduleDocument::class, 'event_schedule_document_user')
            ->withTimestamps()
            ->withPivot('id')
            ->using(EventScheduleDocumentUser::class);
    }
}
