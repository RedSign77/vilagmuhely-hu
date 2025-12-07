<?php

namespace Webtechsolutions\ContentEngine\Models;

use Illuminate\Database\Eloquent\Model;

class WorldZone extends Model
{
    protected $fillable = [
        'zone_key',
        'name',
        'zone_type',
        'min_x',
        'max_x',
        'min_y',
        'max_y',
        'unlock_at',
        'color',
        'is_unlocked',
    ];

    protected $casts = [
        'min_x' => 'integer',
        'max_x' => 'integer',
        'min_y' => 'integer',
        'max_y' => 'integer',
        'unlock_at' => 'integer',
        'is_unlocked' => 'boolean',
    ];

    /**
     * Check if position is within this zone
     */
    public function containsPosition(int $x, int $y): bool
    {
        return $x >= $this->min_x && $x <= $this->max_x
            && $y >= $this->min_y && $y <= $this->max_y;
    }

    /**
     * Get zone center coordinates
     */
    public function getCenterAttribute(): array
    {
        return [
            'x' => (int) (($this->min_x + $this->max_x) / 2),
            'y' => (int) (($this->min_y + $this->max_y) / 2),
        ];
    }

    /**
     * Scope to get unlocked zones
     */
    public function scopeUnlocked($query)
    {
        return $query->where('is_unlocked', true);
    }

    /**
     * Scope to get locked zones
     */
    public function scopeLocked($query)
    {
        return $query->where('is_unlocked', false);
    }
}
