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
        'customization',
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
        'customization' => 'array',
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

    /**
     * Get default customization for a structure type
     */
    public static function getDefaultCustomization(string $type): array
    {
        return match ($type) {
            self::TYPE_COTTAGE => [
                'name' => 'Cozy Cottage',
                'description' => 'A humble dwelling where ideas take their first breath.',
                'colors' => ['primary' => '#8B4513', 'roof' => '#654321', 'door' => '#4A3728'],
                'style' => ['roof' => 'thatched', 'windows' => 'round'],
                'features' => ['smoke' => true, 'decoration' => 'none'],
            ],
            self::TYPE_WORKSHOP => [
                'name' => "Creator's Workshop",
                'description' => 'Where digital artifacts are forged with care.',
                'colors' => ['primary' => '#4169E1', 'glow' => '#FFD700', 'chimney' => '#333333'],
                'style' => ['type' => 'industrial', 'chimneys' => 1],
                'features' => ['gears' => true, 'sign' => 'WORKSHOP'],
            ],
            self::TYPE_GALLERY => [
                'name' => 'Art Gallery',
                'description' => 'A sanctuary where visual stories come alive.',
                'colors' => ['primary' => '#FF69B4', 'banner' => '#FFFFFF', 'trim' => '#FFD700'],
                'style' => ['architecture' => 'classical', 'entrance' => 'columns', 'roof' => 'domed'],
                'features' => ['spotlight' => true],
            ],
            self::TYPE_LIBRARY => [
                'name' => 'Knowledge Library',
                'description' => 'Ancient wisdom meets modern tales within these walls.',
                'colors' => ['primary' => '#2E8B57', 'books' => '#8B0000', 'trim' => '#D4AF37'],
                'style' => ['type' => 'classic', 'windows' => 'tall'],
                'features' => ['ivy' => true, 'lanterns' => 2],
            ],
            self::TYPE_ACADEMY => [
                'name' => 'Grand Academy',
                'description' => 'Where masters share their craft and wisdom flows freely.',
                'colors' => ['primary' => '#FFA500', 'flag' => '#FFFFFF', 'roof' => '#8B4513'],
                'style' => ['architecture' => 'institutional', 'towers' => 1, 'emblem' => 'book'],
                'features' => ['bell' => true, 'courtyard' => false],
            ],
            self::TYPE_TOWER => [
                'name' => 'Mystic Tower',
                'description' => 'Where realms are born and adventures await.',
                'colors' => ['primary' => '#9370DB', 'glow' => '#00FFFF', 'spire' => '#4B0082'],
                'style' => ['type' => 'wizard', 'spire' => 'pointed'],
                'features' => ['runes' => true, 'glow_intensity' => 0.5, 'weather' => 'none'],
            ],
            self::TYPE_MONUMENT => [
                'name' => 'Victory Monument',
                'description' => 'A testament to great deeds and lasting legacy.',
                'colors' => ['primary' => '#FFD700', 'base' => '#FFFFFF', 'accent' => '#C0C0C0'],
                'style' => ['type' => 'obelisk', 'material' => 'marble'],
                'features' => ['pedestal' => true, 'light_beam' => false, 'particles' => 'sparkle'],
                'text' => ['inscription' => ''],
            ],
            self::TYPE_GARDEN => [
                'name' => 'Peaceful Garden',
                'description' => 'A place of growth where community bonds flourish.',
                'colors' => ['primary' => '#90EE90', 'flowers' => '#FF6B6B', 'path' => '#DEB887'],
                'style' => ['type' => 'zen', 'tree' => 'oak', 'season' => 'spring'],
                'features' => ['water' => false, 'bench' => true, 'fireflies' => false],
            ],
            self::TYPE_ORIGIN => [
                'name' => 'Origin Monument',
                'description' => 'Where it all began. The heart of our shared world.',
                'colors' => ['primary' => '#FFFFFF', 'glow' => '#FFD700', 'base' => '#C0C0C0'],
                'style' => ['type' => 'obelisk', 'material' => 'crystal'],
                'features' => ['light_beam' => true, 'particles' => 'sparkle'],
            ],
            default => [
                'name' => 'Structure',
                'description' => 'A building in the world.',
                'colors' => ['primary' => '#94A3B8'],
                'style' => [],
                'features' => [],
            ],
        };
    }

    /**
     * Get the customization with defaults merged
     */
    public function getCustomizationWithDefaults(): array
    {
        $defaults = self::getDefaultCustomization($this->structure_type);
        $custom = $this->customization ?? [];

        return array_replace_recursive($defaults, $custom);
    }

    /**
     * Get display name (custom or default)
     */
    public function getDisplayNameAttribute(): string
    {
        $customization = $this->getCustomizationWithDefaults();

        return $customization['name'] ?? $this->type_name;
    }

    /**
     * Get display description (custom or default)
     */
    public function getDisplayDescriptionAttribute(): string
    {
        $customization = $this->getCustomizationWithDefaults();

        return $customization['description'] ?? '';
    }

    /**
     * Get primary color (custom or default)
     */
    public function getPrimaryColorAttribute(): string
    {
        $customization = $this->getCustomizationWithDefaults();

        return $customization['colors']['primary'] ?? $this->color;
    }
}
