<?php

namespace App\Models;

use App\Utils\Constants\ConfigMembership;
use App\Utils\Helper;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|static filter(array $filters = []) // scope Filter query builder
 * @method static \Illuminate\Database\Eloquent\Builder|static sortBy(string $sortBy = '') // scope SortBy query builder
 */
class Membership extends Model
{
    use  SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'membership';

    protected $fillable = [
        'id',
        'type',
        'name',
        'description',
        'price',
        'duration',
        'badge',
        'sort',
        'badge_color_background',
        'badge_color_text',
        'config',
        'status',
        'organizer_id'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'config' => 'array',
            'status' => 'boolean'
        ];
    }

    public function scopeFilter(Builder $query, array $filters = [])
    {
        if (array_key_exists('status', $filters)) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['duration'])) {
            $query->where('duration', $filters['duration']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['organizer_id'])) {
            $query->where('organizer_id', $filters['organizer_id']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }
    }

    public function scopeSortBy(Builder $query, string $sortBy = '')
    {
        switch ($sortBy) {
            case 'sort':
                $query->orderBy('sort', 'asc');
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
    }

    public function getConfig(ConfigMembership $key, $default = null)
    {
        return $this->config[$key->value] ?? $default;
    }

    public function hasFeature(ConfigMembership $key): bool
    {
        return (bool) $this->getConfig($key, false);
    }

    public function allowComment(): bool
    {
        return $this->hasFeature(ConfigMembership::ALLOW_COMMENT);
    }

    public function allowChooseSeat(): bool
    {
        return $this->hasFeature(ConfigMembership::ALLOW_CHOOSE_SEAT);
    }

    public function allowDocumentary(): bool
    {
        return $this->hasFeature(ConfigMembership::ALLOW_DOCUMENTARY);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'membership_user')
            ->withPivot(['start_date', 'end_date', 'status'])
            ->withTimestamps();
    }
}
