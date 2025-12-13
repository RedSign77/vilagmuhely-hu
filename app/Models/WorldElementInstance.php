<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldElementInstance extends Model
{
    protected $fillable = [
        'world_element_type_id',
        'position_x',
        'position_y',
        'rotation',
        'scale',
        'variant',
        'biome',
        'is_interactable',
        'interaction_count',
        'metadata',
    ];

    protected $casts = [
        'world_element_type_id' => 'integer',
        'position_x' => 'integer',
        'position_y' => 'integer',
        'rotation' => 'decimal:2',
        'scale' => 'decimal:2',
        'is_interactable' => 'boolean',
        'interaction_count' => 'integer',
        'metadata' => 'array',
    ];

    protected $with = ['type'];

    /**
     * Get the element type
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(WorldElementType::class, 'world_element_type_id');
    }

    /**
     * Scope to filter elements within viewport bounds
     */
    public function scopeInViewport($query, int $minX, int $maxX, int $minY, int $maxY)
    {
        return $query->where('position_x', '>=', $minX)
            ->where('position_x', '<=', $maxX)
            ->where('position_y', '>=', $minY)
            ->where('position_y', '<=', $maxY);
    }

    /**
     * Scope to filter by biome
     */
    public function scopeByBiome($query, string $biome)
    {
        return $query->where('biome', $biome);
    }

    /**
     * Scope to filter interactable elements
     */
    public function scopeInteractable($query)
    {
        return $query->where('is_interactable', true);
    }

    /**
     * Increment interaction count
     */
    public function incrementInteraction(): void
    {
        $this->increment('interaction_count');
    }

    /**
     * Get position as array
     */
    public function getPositionAttribute(): array
    {
        return [
            'x' => $this->position_x,
            'y' => $this->position_y,
        ];
    }

    /**
     * Calculate distance from a point
     */
    public function distanceFrom(int $x, int $y): float
    {
        return sqrt(
            pow($this->position_x - $x, 2) +
            pow($this->position_y - $y, 2)
        );
    }

    /**
     * Check if element is within radius of a point
     */
    public function isWithinRadius(int $x, int $y, int $radius): bool
    {
        return $this->distanceFrom($x, $y) <= $radius;
    }

    /**
     * Get formatted position string
     */
    public function getFormattedPositionAttribute(): string
    {
        return "({$this->position_x}, {$this->position_y})";
    }
}
