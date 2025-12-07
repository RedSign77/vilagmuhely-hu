<?php

namespace Webtechsolutions\ContentEngine\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldStructure extends Model
{
    protected $fillable = [
        'user_id',
        'structure_type',
        'category_slug',
        'grid_x',
        'grid_y',
        'level',
        'health',
        'decay_state',
        'metadata',
        'placed_at',
        'last_owner_activity',
        'decay_started_at',
    ];

    protected $casts = [
        'grid_x' => 'integer',
        'grid_y' => 'integer',
        'level' => 'integer',
        'health' => 'integer',
        'metadata' => 'array',
        'placed_at' => 'datetime',
        'last_owner_activity' => 'datetime',
        'decay_started_at' => 'datetime',
    ];

    // Structure type constants
    public const TYPE_COTTAGE = 'cottage';
    public const TYPE_WORKSHOP = 'workshop';
    public const TYPE_GALLERY = 'gallery';
    public const TYPE_LIBRARY = 'library';
    public const TYPE_ACADEMY = 'academy';
    public const TYPE_TOWER = 'tower';
    public const TYPE_MONUMENT = 'monument';
    public const TYPE_GARDEN = 'garden';
    public const TYPE_ORIGIN = 'origin_monument';
    public const TYPE_LEGACY_CRYSTAL = 'legacy_crystal';

    // Decay state constants
    public const DECAY_ACTIVE = 'active';
    public const DECAY_FADING = 'fading';
    public const DECAY_RUINED = 'ruined';

    /**
     * Get the user who owns this structure
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get structure costs
     */
    public static function getStructureCosts(string $type): array
    {
        return match ($type) {
            self::TYPE_COTTAGE => ['stone' => 5, 'wood' => 3],
            self::TYPE_WORKSHOP => ['stone' => 10, 'crystal_shards' => 5],
            self::TYPE_GALLERY => ['wood' => 8, 'crystal_shards' => 8],
            self::TYPE_LIBRARY => ['wood' => 15, 'stone' => 5],
            self::TYPE_ACADEMY => ['stone' => 20, 'wood' => 10],
            self::TYPE_TOWER => ['crystal_shards' => 15, 'magic_essence' => 10],
            self::TYPE_MONUMENT => ['stone' => 30, 'wood' => 30, 'crystal_shards' => 30, 'magic_essence' => 30],
            self::TYPE_GARDEN => ['wood' => 5, 'magic_essence' => 5],
            default => [],
        };
    }

    /**
     * Get structure display name
     */
    public function getTypeNameAttribute(): string
    {
        return match ($this->structure_type) {
            self::TYPE_COTTAGE => 'Cottage',
            self::TYPE_WORKSHOP => 'Workshop',
            self::TYPE_GALLERY => 'Gallery',
            self::TYPE_LIBRARY => 'Library',
            self::TYPE_ACADEMY => 'Academy',
            self::TYPE_TOWER => 'Tower',
            self::TYPE_MONUMENT => 'Monument',
            self::TYPE_GARDEN => 'Garden',
            self::TYPE_ORIGIN => 'Origin Monument',
            self::TYPE_LEGACY_CRYSTAL => 'Legacy Crystal',
            default => ucfirst($this->structure_type),
        };
    }

    /**
     * Get structure color based on type
     */
    public function getColorAttribute(): string
    {
        return match ($this->structure_type) {
            self::TYPE_COTTAGE => '#8b4513', // Brown
            self::TYPE_WORKSHOP => '#4169e1', // Blue
            self::TYPE_GALLERY => '#ff69b4', // Pink
            self::TYPE_LIBRARY => '#2e8b57', // Green
            self::TYPE_ACADEMY => '#ffa500', // Orange
            self::TYPE_TOWER => '#9370db', // Purple
            self::TYPE_MONUMENT => '#ffd700', // Gold
            self::TYPE_GARDEN => '#90ee90', // Light green
            self::TYPE_ORIGIN => '#ffffff', // White
            self::TYPE_LEGACY_CRYSTAL => '#00ffff', // Cyan
            default => '#94a3b8', // Gray
        };
    }

    /**
     * Check if structure is decaying
     */
    public function isDecaying(): bool
    {
        return $this->decay_state !== self::DECAY_ACTIVE;
    }

    /**
     * Refresh structure (reset decay)
     */
    public function refresh(): void
    {
        $this->update([
            'decay_state' => self::DECAY_ACTIVE,
            'last_owner_activity' => now(),
            'decay_started_at' => null,
        ]);
    }

    /**
     * Scope to get active structures
     */
    public function scopeActive($query)
    {
        return $query->where('decay_state', self::DECAY_ACTIVE);
    }

    /**
     * Scope to get structures in area
     */
    public function scopeInArea($query, int $minX, int $maxX, int $minY, int $maxY)
    {
        return $query->whereBetween('grid_x', [$minX, $maxX])
                     ->whereBetween('grid_y', [$minY, $maxY]);
    }

    /**
     * Scope to get structures by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('structure_type', $type);
    }
}
