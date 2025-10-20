<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Utils\Helper;

class Config extends Model
{
    protected $fillable = [
        'config_key',
        'config_type',
        'config_value',
        'description',
        'organizer_id'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Helper::getTimestampAsId();
            }
        });
    }
}
