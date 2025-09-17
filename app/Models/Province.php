<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'division_type',
    ];

    public function districts(): HasMany
    {
        return $this->hasMany(District::class, 'province_code', 'code');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'province_code', 'code');
    }
}
