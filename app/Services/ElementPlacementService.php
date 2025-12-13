<?php

namespace App\Services;

use App\Models\WorldElementInstance;
use App\Models\WorldElementType;
use App\Models\WorldMapConfig;

class ElementPlacementService
{
    /**
     * Place an element at specific coordinates with collision check
     */
    public function placeElement(
        WorldElementType $elementType,
        int $x,
        int $y,
        array $options = []
    ): ?WorldElementInstance {
        // Extract options
        $biome = $options['biome'] ?? null;
        $variant = $options['variant'] ?? null;
        $forcePlace = $options['force'] ?? false;

        // Check collision unless forced
        if (!$forcePlace && $this->checkCollision($x, $y, $this->getMinimumSpacing($elementType))) {
            return null; // Position occupied
        }

        // Apply random variations
        $rotation = $this->getRandomRotation();
        $scale = $this->getRandomScale($elementType);

        // Create the element instance
        $instance = WorldElementInstance::create([
            'world_element_type_id' => $elementType->id,
            'position_x' => $x,
            'position_y' => $y,
            'rotation' => $rotation,
            'scale' => $scale,
            'variant' => $variant,
            'biome' => $biome,
            'is_interactable' => true,
            'interaction_count' => 0,
            'metadata' => $options['metadata'] ?? null,
        ]);

        return $instance;
    }

    /**
     * Find a valid position for an element in a specific biome
     */
    public function findValidPosition(
        string $biome,
        WorldElementType $elementType,
        int $maxAttempts = 100
    ): ?array {
        $config = WorldMapConfig::getInstance();
        $bounds = $config->getBounds();

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $x = mt_rand($bounds['minX'], $bounds['maxX']);
            $y = mt_rand($bounds['minY'], $bounds['maxY']);

            // Check if position is valid (no collision)
            if (!$this->checkCollision($x, $y, $this->getMinimumSpacing($elementType))) {
                return ['x' => $x, 'y' => $y];
            }
        }

        return null; // Failed to find valid position
    }

    /**
     * Check if there's a collision at the given position
     * Returns true if collision detected (position occupied)
     * Returns false if position is free
     */
    public function checkCollision(int $x, int $y, int $radius): bool
    {
        // Check if any elements exist within the radius
        $exists = WorldElementInstance::query()
            ->where('position_x', '>=', $x - $radius)
            ->where('position_x', '<=', $x + $radius)
            ->where('position_y', '>=', $y - $radius)
            ->where('position_y', '<=', $y + $radius)
            ->exists();

        return $exists;
    }

    /**
     * Get minimum spacing for an element type
     */
    protected function getMinimumSpacing(WorldElementType $elementType): int
    {
        // Larger elements need more spacing
        // Base spacing on rarity and category
        return match ($elementType->rarity) {
            'legendary' => 10,
            'epic' => 8,
            'rare' => 5,
            'uncommon' => 3,
            default => 2, // common
        };
    }

    /**
     * Get random rotation (0-360 degrees)
     */
    protected function getRandomRotation(): float
    {
        return mt_rand(0, 360);
    }

    /**
     * Get random scale with variation
     */
    protected function getRandomScale(WorldElementType $elementType): float
    {
        // Base scale: 0.8 to 1.2 (±20% variation)
        // Rare elements can be slightly larger
        $baseMin = match ($elementType->rarity) {
            'legendary' => 1.0,
            'epic' => 0.9,
            default => 0.8,
        };

        $baseMax = match ($elementType->rarity) {
            'legendary' => 1.5,
            'epic' => 1.3,
            default => 1.2,
        };

        // Random value between min and max
        $scale = $baseMin + (mt_rand(0, 100) / 100) * ($baseMax - $baseMin);

        return round($scale, 2);
    }

    /**
     * Calculate density for a biome and element type
     * Returns a multiplier for element spawning
     */
    public function calculateDensity(string $biome, WorldElementType $elementType): float
    {
        // Base density from element type
        $baseDensity = $elementType->density_weight;

        // Biome-specific multipliers
        $biomeMultiplier = $this->getBiomeMultiplier($biome, $elementType->category);

        return $baseDensity * $biomeMultiplier;
    }

    /**
     * Get biome multiplier for element category
     */
    protected function getBiomeMultiplier(string $biome, string $category): float
    {
        // Define which element categories thrive in which biomes
        $biomePreferences = [
            'forest' => [
                'vegetation' => 2.0, // Trees and plants thrive
                'water' => 0.8,
                'terrain' => 1.0,
                'structure' => 0.5,
                'decoration' => 0.7,
            ],
            'meadow' => [
                'vegetation' => 1.5, // Flowers and grass thrive
                'water' => 1.0,
                'terrain' => 0.8,
                'structure' => 1.0,
                'decoration' => 1.2,
            ],
            'desert' => [
                'vegetation' => 0.2, // Very few plants
                'water' => 0.1, // Very rare water
                'terrain' => 2.0, // Lots of rocks and sand
                'structure' => 0.8,
                'decoration' => 0.5,
            ],
            'swamp' => [
                'vegetation' => 1.3,
                'water' => 2.5, // Lots of water
                'terrain' => 1.2,
                'structure' => 0.6,
                'decoration' => 0.8,
            ],
            'tundra' => [
                'vegetation' => 0.5, // Sparse vegetation
                'water' => 0.3, // Frozen water
                'terrain' => 1.8, // Lots of rocks and ice
                'structure' => 0.7,
                'decoration' => 0.4,
            ],
        ];

        return $biomePreferences[$biome][$category] ?? 1.0;
    }

    /**
     * Apply variation to an existing element
     */
    public function applyVariation(WorldElementInstance $element): WorldElementInstance
    {
        $element->update([
            'rotation' => $this->getRandomRotation(),
            'scale' => $this->getRandomScale($element->type),
        ]);

        return $element->fresh();
    }

    /**
     * Get elements within a radius of a point
     */
    public function getElementsInRadius(int $x, int $y, int $radius): \Illuminate\Database\Eloquent\Collection
    {
        return WorldElementInstance::query()
            ->where('position_x', '>=', $x - $radius)
            ->where('position_x', '<=', $x + $radius)
            ->where('position_y', '>=', $y - $radius)
            ->where('position_y', '<=', $y + $radius)
            ->get()
            ->filter(function ($element) use ($x, $y, $radius) {
                // Filter by actual circular radius
                return $element->distanceFrom($x, $y) <= $radius;
            });
    }

    /**
     * Count elements in a specific area
     */
    public function countElementsInArea(int $minX, int $maxX, int $minY, int $maxY): int
    {
        return WorldElementInstance::query()
            ->where('position_x', '>=', $minX)
            ->where('position_x', '<=', $maxX)
            ->where('position_y', '>=', $minY)
            ->where('position_y', '<=', $maxY)
            ->count();
    }

    /**
     * Remove element at position
     */
    public function removeElementAt(int $x, int $y, int $tolerance = 1): bool
    {
        $deleted = WorldElementInstance::query()
            ->where('position_x', '>=', $x - $tolerance)
            ->where('position_x', '<=', $x + $tolerance)
            ->where('position_y', '>=', $y - $tolerance)
            ->where('position_y', '<=', $y + $tolerance)
            ->delete();

        return $deleted > 0;
    }

    /**
     * Batch place multiple elements efficiently
     */
    public function batchPlaceElements(array $placements): int
    {
        $placed = 0;

        foreach ($placements as $placement) {
            $element = $this->placeElement(
                $placement['type'],
                $placement['x'],
                $placement['y'],
                $placement['options'] ?? []
            );

            if ($element) {
                $placed++;
            }
        }

        return $placed;
    }
}
