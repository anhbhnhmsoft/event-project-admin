<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $fillable = [
        'config_key',
        'config_type',
        'config_value',
        'description',
    ];
}
