<?php

namespace Webtechsolutions\ContentEngine\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Webtechsolutions\ContentEngine\Models\WorldActivityLog;
use Webtechsolutions\ContentEngine\Models\WorldStructure;

class WorldBuilderService
{
    protected WorldResourceService $resourceService;
    protected AdjacencyService $adjacencyService;
    protected ZoneService $zoneService;

    public function __construct(
        WorldResourceService $resourceService,
        AdjacencyService $adjacencyService,
        ZoneService $zoneService
    ) {
        $this->resourceService = $resourceService;
        $this->adjacencyService = $adjacencyService;
        $this->zoneService = $zoneService;
    }

    /**
     * Place new structure
     */
    public function placeStructure(
        User $user,
        string $type,
        int $x,
        int $y,
        ?array $metadata = null
    ): WorldStructure {
        // Validate position
        if (!$this->adjacencyService->isValidPosition($x, $y)) {
            throw new \InvalidArgumentException('Invalid position for building');
        }

        // Get costs
        $costs = WorldStructure::getStructureCosts($type);

        // Check if user can afford
        if (!$this->resourceService->canAfford($user, $type)) {
            throw new \InvalidArgumentException('Insufficient resources');
        }

        // Spend resources
        if (!$this->resourceService->spendResources($user, $costs)) {
            throw new \RuntimeException('Failed to spend resources');
        }

        // Create structure
        $structure = WorldStructure::create([
            'user_id' => $user->id,
            'structure_type' => $type,
            'category_slug' => $metadata['category_slug'] ?? null,
            'grid_x' => $x,
            'grid_y' => $y,
            'level' => 1,
            'health' => 100,
            'decay_state' => WorldStructure::DECAY_ACTIVE,
            'metadata' => $metadata,
            'placed_at' => now(),
            'last_owner_activity' => now(),
        ]);

        // Update user stats
        $userResources = $this->resourceService->getResources($user);
        $userResources->increment('total_structures_built');

        // Log activity
        WorldActivityLog::log(
            $user->id,
            WorldActivityLog::TYPE_STRUCTURE_PLACED,
            $structure->id,
            [
                'type' => $type,
                'position' => ['x' => $x, 'y' => $y],
                'costs' => $costs,
            ]
        );

        return $structure;
    }

    /**
     * Upgrade existing structure
     */
    public function upgradeStructure(WorldStructure $structure): WorldStructure
    {
        // Calculate upgrade cost (increases with level)
        $baseCosts = WorldStructure::getStructureCosts($structure->structure_type);
        $upgradeCosts = [];

        foreach ($baseCosts as $resource => $amount) {
            $upgradeCosts[$resource] = (int) ($amount * 1.5 * $structure->level);
        }

        // Check if user can afford
        $user = $structure->user;
        $userResources = $this->resourceService->getResources($user);

        if (!$userResources->canAfford($upgradeCosts)) {
            throw new \InvalidArgumentException('Insufficient resources for upgrade');
        }

        // Spend resources
        if (!$userResources->spendResources($upgradeCosts)) {
            throw new \RuntimeException('Failed to spend resources');
        }

        // Upgrade structure
        $previousLevel = $structure->level;
        $structure->update([
            'level' => $structure->level + 1,
            'health' => 100,
            'last_owner_activity' => now(),
        ]);

        // Update user stats
        $userResources->increment('total_upgrades_done');

        // Log activity
        WorldActivityLog::log(
            $user->id,
            WorldActivityLog::TYPE_STRUCTURE_UPGRADED,
            $structure->id,
            [
                'previous_level' => $previousLevel,
                'new_level' => $structure->level,
                'costs' => $upgradeCosts,
            ]
        );

        return $structure->fresh();
    }

    /**
     * Check if grid position is available
     */
    public function isPositionAvailable(int $x, int $y): bool
    {
        return $this->adjacencyService->isValidPosition($x, $y);
    }

    /**
     * Get nearby structures (for zone calculation)
     */
    public function getNearbyStructures(int $x, int $y, int $radius = 3): Collection
    {
        $minX = $x - $radius;
        $maxX = $x + $radius;
        $minY = $y - $radius;
        $maxY = $y + $radius;

        return WorldStructure::inArea($minX, $maxX, $minY, $maxY)
            ->active()
            ->get();
    }

    /**
     * Calculate zone bonuses for position
     */
    public function calculateZoneBonuses(int $x, int $y): array
    {
        $nearbyStructures = $this->getNearbyStructures($x, $y);
        $typeCount = $nearbyStructures->groupBy('structure_type')->map->count();

        $bonuses = [];

        // Diversity bonus
        if ($typeCount->count() >= 3) {
            $bonuses['diversity'] = 'Diverse neighborhood (+10% resource generation)';
        }

        // Same type cluster bonus
        foreach ($typeCount as $type => $count) {
            if ($count >= 3) {
                $bonuses['cluster_' . $type] = ucfirst($type) . ' district forming';
            }
        }

        return $bonuses;
    }

    /**
     * Find best available positions for new structure
     */
    public function suggestPositions(User $user, string $structureType, int $limit = 5): array
    {
        $availableCells = $this->adjacencyService->findAvailableAdjacentCells(50);
        $userStructures = WorldStructure::where('user_id', $user->id)
            ->active()
            ->get(['grid_x', 'grid_y']);

        $scored = [];

        foreach ($availableCells as $cell) {
            $score = 0;

            // Prefer positions near user's existing structures
            if ($userStructures->isNotEmpty()) {
                $minDistance = PHP_INT_MAX;
                foreach ($userStructures as $structure) {
                    $distance = sqrt(
                        pow($cell['x'] - $structure->grid_x, 2) +
                        pow($cell['y'] - $structure->grid_y, 2)
                    );
                    $minDistance = min($minDistance, $distance);
                }
                $score += max(0, 10 - $minDistance); // Closer = higher score
            }

            // Bonus for zone bonuses
            $bonuses = $this->calculateZoneBonuses($cell['x'], $cell['y']);
            $score += count($bonuses) * 2;

            $scored[] = [
                'x' => $cell['x'],
                'y' => $cell['y'],
                'score' => $score,
                'bonuses' => $bonuses,
            ];
        }

        // Sort by score and return top positions
        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $limit);
    }

    /**
     * Remove structure (admin only or decay)
     */
    public function removeStructure(WorldStructure $structure, string $reason = 'manual'): bool
    {
        // Log removal
        WorldActivityLog::log(
            $structure->user_id,
            WorldActivityLog::TYPE_STRUCTURE_REMOVED,
            $structure->id,
            [
                'type' => $structure->structure_type,
                'position' => ['x' => $structure->grid_x, 'y' => $structure->grid_y],
                'reason' => $reason,
                'level' => $structure->level,
            ]
        );

        return $structure->delete();
    }
}
