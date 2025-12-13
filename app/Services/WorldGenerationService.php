<?php

namespace App\Services;

use App\Models\WorldElementInstance;
use App\Models\WorldElementType;
use App\Models\WorldMapConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorldGenerationService
{
    protected ElementPlacementService $placementService;

    public function __construct(ElementPlacementService $placementService)
    {
        $this->placementService = $placementService;
    }

    /**
     * Generate biome map using simple radial zones
     * Returns a biome for given coordinates
     */
    public function getBiomeAt(int $x, int $y, WorldMapConfig $config): string
    {
        // Calculate distance from center (0, 0)
        $distance = sqrt($x * $x + $y * $y);

        // Define biome zones based on distance from center
        // Center: Meadow (peaceful starting area)
        // Expanding outward: Forest, Swamp, Desert, Tundra
        $maxDistance = sqrt(($config->map_width / 2) ** 2 + ($config->map_height / 2) ** 2);
        $normalizedDistance = $distance / $maxDistance;

        if ($normalizedDistance < 0.2) {
            return 'meadow'; // Center 20%
        } elseif ($normalizedDistance < 0.4) {
            return 'forest'; // 20-40%
        } elseif ($normalizedDistance < 0.6) {
            // Mix of swamp and meadow based on angle
            $angle = atan2($y, $x);
            return ($angle > 0) ? 'swamp' : 'meadow';
        } elseif ($normalizedDistance < 0.8) {
            return 'desert'; // 60-80%
        } else {
            return 'tundra'; // Outer 20%
        }
    }

    /**
     * Generate map biome distribution
     * Returns array of biome statistics
     */
    public function generateBiomeMap(int $width, int $height): array
    {
        $config = WorldMapConfig::getInstance();
        $biomeCount = [
            'meadow' => 0,
            'forest' => 0,
            'swamp' => 0,
            'desert' => 0,
            'tundra' => 0,
        ];

        $minX = -($width / 2);
        $maxX = $width / 2;
        $minY = -($height / 2);
        $maxY = $height / 2;

        // Sample biome distribution (check every 5th cell for performance)
        for ($x = $minX; $x < $maxX; $x += 5) {
            for ($y = $minY; $y < $maxY; $y += 5) {
                $biome = $this->getBiomeAt($x, $y, $config);
                $biomeCount[$biome]++;
            }
        }

        return $biomeCount;
    }

    /**
     * Main element generation orchestration
     */
    public function generateElements(array $options = []): array
    {
        $config = WorldMapConfig::getInstance();

        // Extract options
        $regenerate = $options['regenerate'] ?? false;
        $specificBiome = $options['biome'] ?? null;
        $densityMultiplier = $options['density'] ?? 'medium';
        $seed = $options['seed'] ?? null;

        // Set density multiplier
        $densityValue = match ($densityMultiplier) {
            'low' => 0.5,
            'high' => 1.5,
            default => 1.0, // medium
        };

        // Clear existing elements if regenerating
        if ($regenerate) {
            $this->clearMap();
        }

        // Update generation seed
        if ($seed) {
            mt_srand(crc32($seed));
            $config->updateGenerationSeed($seed);
        } else {
            $seed = uniqid('seed_', true);
            mt_srand(crc32($seed));
            $config->updateGenerationSeed($seed);
        }

        // Get map bounds
        $bounds = $config->getBounds();

        // Calculate total map area
        $mapArea = $config->map_width * $config->map_height;

        // Get active element types
        $elementTypes = WorldElementType::active()->get();

        if ($elementTypes->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No active element types found. Please create element types first.',
                'generated' => 0,
            ];
        }

        // Statistics
        $stats = [
            'total_generated' => 0,
            'by_category' => [],
            'by_biome' => [],
            'by_rarity' => [],
        ];

        // Generate elements for each type
        foreach ($elementTypes as $elementType) {
            // Calculate how many of this element to place
            $elementCount = $this->calculateElementCount(
                $mapArea,
                $elementType->density_weight,
                $densityValue
            );

            // Skip if count is too low
            if ($elementCount < 1) {
                continue;
            }

            // Place elements
            for ($i = 0; $i < $elementCount; $i++) {
                $instance = $this->placeRandomElement($elementType, $config, $specificBiome);

                if ($instance) {
                    $stats['total_generated']++;

                    // Track statistics
                    $category = $elementType->category;
                    $biome = $instance->biome ?? 'unknown';
                    $rarity = $elementType->rarity;

                    $stats['by_category'][$category] = ($stats['by_category'][$category] ?? 0) + 1;
                    $stats['by_biome'][$biome] = ($stats['by_biome'][$biome] ?? 0) + 1;
                    $stats['by_rarity'][$rarity] = ($stats['by_rarity'][$rarity] ?? 0) + 1;
                }
            }
        }

        Log::info('World elements generated', $stats);

        return [
            'success' => true,
            'message' => "Generated {$stats['total_generated']} elements successfully",
            'stats' => $stats,
            'generated' => $stats['total_generated'],
        ];
    }

    /**
     * Calculate how many of this element type should be placed
     */
    protected function calculateElementCount(int $mapArea, float $densityWeight, float $densityMultiplier): int
    {
        // Base calculation: map area * density weight * multiplier / 10000
        // This gives roughly: 200x200 map with density 1.0 = 4 elements
        $count = ($mapArea * $densityWeight * $densityMultiplier) / 10000;

        // Add some randomness (±20%)
        $variance = $count * 0.2;
        $count = $count + (mt_rand(-100, 100) / 100) * $variance;

        return max(0, (int) round($count));
    }

    /**
     * Place a single element randomly on the map
     */
    protected function placeRandomElement(
        WorldElementType $elementType,
        WorldMapConfig $config,
        ?string $specificBiome = null
    ): ?WorldElementInstance {
        $bounds = $config->getBounds();
        $maxAttempts = 50; // Try up to 50 times to find a valid position

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            // Random position within bounds
            $x = mt_rand($bounds['minX'], $bounds['maxX']);
            $y = mt_rand($bounds['minY'], $bounds['maxY']);

            // Determine biome at this position
            $biome = $this->getBiomeAt($x, $y, $config);

            // If specific biome requested, skip if doesn't match
            if ($specificBiome && $biome !== $specificBiome) {
                continue;
            }

            // Check if element can spawn in this biome
            if (!$elementType->canSpawnInBiome($biome)) {
                continue;
            }

            // Check collision with existing elements
            if (!$this->placementService->checkCollision($x, $y, 2)) {
                // Position is valid, place element
                return $this->placementService->placeElement($elementType, $x, $y, [
                    'biome' => $biome,
                ]);
            }
        }

        // Failed to find valid position after max attempts
        return null;
    }

    /**
     * Clear all element instances from the map
     */
    public function clearMap(): int
    {
        $count = WorldElementInstance::count();
        WorldElementInstance::truncate();

        Log::info("Cleared {$count} elements from map");

        return $count;
    }

    /**
     * Regenerate the entire map (clear + generate)
     */
    public function regenerateMap(array $options = []): array
    {
        $options['regenerate'] = true;

        return $this->generateElements($options);
    }

    /**
     * Get generation statistics
     */
    public function getGenerationStats(): array
    {
        $config = WorldMapConfig::getInstance();

        return [
            'total_elements' => WorldElementInstance::count(),
            'by_category' => WorldElementInstance::select('world_element_types.category', DB::raw('count(*) as count'))
                ->join('world_element_types', 'world_element_instances.world_element_type_id', '=', 'world_element_types.id')
                ->groupBy('world_element_types.category')
                ->pluck('count', 'category')
                ->toArray(),
            'by_biome' => WorldElementInstance::select('biome', DB::raw('count(*) as count'))
                ->whereNotNull('biome')
                ->groupBy('biome')
                ->pluck('count', 'biome')
                ->toArray(),
            'last_regenerated' => $config->last_regenerated_at?->toDateTimeString(),
            'generation_seed' => $config->generation_seed,
        ];
    }
}
