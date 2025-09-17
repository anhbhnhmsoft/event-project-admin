<?php

namespace App\Models;

use App\Utils\Constants\ConfigMembership;
use App\Utils\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
