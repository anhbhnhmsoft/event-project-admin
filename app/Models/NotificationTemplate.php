<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'name',
        'title',
        'description',
        'notification_type',
        'organizer_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Template belongs to an Organizer
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    /**
     * Scope: Filter only active templates
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by organizer
     */
    public function scopeForOrganizer(Builder $query, ?int $organizerId): Builder
    {
        if ($organizerId) {
            return $query->where('organizer_id', $organizerId);
        }
        return $query;
    }
}
