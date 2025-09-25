<?php

namespace App\Models;

use App\Utils\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transactions extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'foreign_id',
        'type',
        'money',
        'transaction_code',
        'transaction_id',
        'description',
        'status',
        'metadata',
        'user_id',
        'expired_at',
        'config_pay'
    ];

    protected function casts(): array
    {
        return [
            'config_pay' => 'array',
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

    public function membershipUser(): BelongsTo
    {
        return $this->belongsTo(MembershipUser::class, 'foreign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
