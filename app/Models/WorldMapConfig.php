<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldMapConfig extends Model
{
    protected $table = 'world_map_config';

    protected $fillable = [
        'map_width',
        'map_height',
        'tile_size',
        'default_biome',
        'generation_seed',
        'last_regenerated_at',
        'settings',
    ];

    protected $casts = [
        'map_width' => 'integer',
        'map_height' => 'integer',
        'tile_size' => 'integer',
        'last_regenerated_at' => 'datetime',
        'settings' => 'array',
    ];

    /**
     * Get the singleton instance of map config
     * Creates one if it doesn't exist
     */
    public static function getInstance(): self
    {
        $config = static::first();

        if (!$config) {
            $config = static::create([
                'map_width' => 200,
                'map_height' => 200,
                'tile_size' => 64,
                'default_biome' => 'meadow',
            ]);
        }

        return $config;
    }

    /**
     * Get map bounds
     */
    public function getBounds(): array
    {
        return [
            'minX' => -($this->map_width / 2),
            'maxX' => $this->map_width / 2,
            'minY' => -($this->map_height / 2),
            'maxY' => $this->map_height / 2,
        ];
    }

    /**
     * Get map center coordinates
     */
    public function getCenter(): array
    {
        return [
            'x' => 0,
            'y' => 0,
        ];
    }

    /**
     * Get total map area (in map units)
     */
    public function getAreaAttribute(): int
    {
        return $this->map_width * $this->map_height;
    }

    /**
     * Check if coordinates are within map bounds
     */
    public function isWithinBounds(int $x, int $y): bool
    {
        $bounds = $this->getBounds();

        return $x >= $bounds['minX'] &&
               $x <= $bounds['maxX'] &&
               $y >= $bounds['minY'] &&
               $y <= $bounds['maxY'];
    }

    /**
     * Update generation seed
     */
    public function updateGenerationSeed(?string $seed = null): void
    {
        $this->update([
            'generation_seed' => $seed ?? uniqid('seed_', true),
            'last_regenerated_at' => now(),
        ]);
    }

    /**
     * Get pixel dimensions
     */
    public function getPixelDimensions(): array
    {
        return [
            'width' => $this->map_width * $this->tile_size,
            'height' => $this->map_height * $this->tile_size,
        ];
    }

    /**
     * Prevent deletion of the singleton
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function () {
            return false; // Prevent deletion
        });
    }
}
