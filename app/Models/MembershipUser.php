<?php

namespace App\Models;

use App\Utils\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipUser extends Model
{
    use HasFactory;

    protected $table = "membership_user";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'membership_id',
        'start_date',
        'end_date',
        'status',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Helper::getTimestampAsId();
        });
    }
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date'
        ];
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function membership() : BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}
