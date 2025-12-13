<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class WorldElementType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'image_path',
        'max_width',
        'max_height',
        'density_weight',
        'rarity',
        'resource_bonus',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'max_width' => 'integer',
        'max_height' => 'integer',
        'density_weight' => 'decimal:2',
        'resource_bonus' => 'array',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the element instances of this type
     */
    public function instances(): HasMany
    {
        return $this->hasMany(WorldElementInstance::class);
    }

    /**
     * Scope to filter by active elements
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by rarity
     */
    public function scopeByRarity($query, string $rarity)
    {
        return $query->where('rarity', $rarity);
    }

    /**
     * Get the full image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return Storage::url($this->image_path);
    }

    /**
     * Check if this element type can spawn in a specific biome
     */
    public function canSpawnInBiome(string $biome): bool
    {
        // Check metadata for biome restrictions
        $biomeRestrictions = $this->metadata['biome_restrictions'] ?? null;

        if (!$biomeRestrictions) {
            return true; // No restrictions, can spawn anywhere
        }

        // If biome_restrictions is an array of allowed biomes
        if (is_array($biomeRestrictions)) {
            return in_array($biome, $biomeRestrictions);
        }

        return true;
    }

    /**
     * Get the resource bonus for this element
     */
    public function getResourceBonus(): array
    {
        return $this->resource_bonus ?? [];
    }

    /**
     * Get bonus type (one_time or repeating)
     */
    public function getBonusType(): string
    {
        return $this->resource_bonus['bonus_type'] ?? 'one_time';
    }

    /**
     * Get cooldown hours for repeating bonuses
     */
    public function getCooldownHours(): int
    {
        return $this->resource_bonus['cooldown_hours'] ?? 24;
    }

    /**
     * Check if element has resource bonuses
     */
    public function hasResourceBonus(): bool
    {
        return !empty($this->resource_bonus) && !empty($this->resource_bonus['resources']);
    }
}
