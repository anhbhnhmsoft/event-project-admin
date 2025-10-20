<?php

namespace App\Models;

use App\Utils\Helper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|static filter(array $filters = []) // scope Filter query builder
 * @method static \Illuminate\Database\Eloquent\Builder|static sortBy(string $sortBy = '') // scope SortBy query builder
 */
class MembershipOrganizer extends Model
{
    use HasFactory;

    protected $table = "membership_organizer";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'organizer_id',
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

    public function scopeFilter(Builder $query, array $filters = [])
    {
        if (!empty($filters['organizer_id'])) {
            $query->where('organizer_id', $filters['organizer_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
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

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class, 'organizer_id');
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}
