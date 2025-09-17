<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'division_type',
        'district_code',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_code', 'code');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'ward_code', 'code');
    }
}
