<?php

namespace Webtechsolutions\ContentEngine\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Webtechsolutions\ContentEngine\Models\WorldStructure;

class AdjacencyService
{
    protected ZoneService $zoneService;

    public function __construct(ZoneService $zoneService)
    {
        $this->zoneService = $zoneService;
    }

    /**
     * Find all valid adjacent cells for building
     */
    public function findAvailableAdjacentCells(int $limit = 100): Collection
    {
        $structures = WorldStructure::active()->get(['grid_x', 'grid_y']);
        $adjacentCells = collect();
        $occupiedCells = $structures->mapWithKeys(fn ($s) => ["{$s->grid_x},{$s->grid_y}" => true])->all();

        foreach ($structures as $structure) {
            $neighbors = $this->getNeighborPositions($structure->grid_x, $structure->grid_y);

            foreach ($neighbors as $neighbor) {
                $key = "{$neighbor['x']},{$neighbor['y']}";

                // Skip if cell is occupied or already in list
                if (isset($occupiedCells[$key]) || $adjacentCells->has($key)) {
                    continue;
                }

                // Check if in unlocked zone
                if (!$this->zoneService->isPositionInUnlockedZone($neighbor['x'], $neighbor['y'])) {
                    continue;
                }

                $adjacentCells->put($key, [
                    'x' => $neighbor['x'],
                    'y' => $neighbor['y'],
                ]);

                if ($adjacentCells->count() >= $limit) {
                    break 2;
                }
            }
        }

        return $adjacentCells->values();
    }

    /**
     * Check if position is adjacent to any structure
     */
    public function isAdjacentToStructure(int $x, int $y): bool
    {
        $neighbors = $this->getNeighborPositions($x, $y);

        foreach ($neighbors as $neighbor) {
            $exists = WorldStructure::where('grid_x', $neighbor['x'])
                ->where('grid_y', $neighbor['y'])
                ->exists();

            if ($exists) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find nearest available position to user's existing structures
     */
    public function findNearestAvailablePosition(User $user): ?array
    {
        $userStructures = WorldStructure::where('user_id', $user->id)
            ->active()
            ->get(['grid_x', 'grid_y']);

        if ($userStructures->isEmpty()) {
            // User has no structures, find any available adjacent cell
            $availableCells = $this->findAvailableAdjacentCells(1);
            return $availableCells->first();
        }

        // Find closest available cell to user's structures
        $availableCells = $this->findAvailableAdjacentCells(50);
        $closestCell = null;
        $minDistance = PHP_INT_MAX;

        foreach ($availableCells as $cell) {
            foreach ($userStructures as $structure) {
                $distance = $this->calculateDistance(
                    $cell['x'],
                    $cell['y'],
                    $structure->grid_x,
                    $structure->grid_y
                );

                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $closestCell = $cell;
                }
            }
        }

        return $closestCell;
    }

    /**
     * Get the origin point (first structure)
     */
    public function getOriginPoint(): array
    {
        $origin = WorldStructure::ofType(WorldStructure::TYPE_ORIGIN)->first();

        if ($origin) {
            return ['x' => $origin->grid_x, 'y' => $origin->grid_y];
        }

        // Default origin at center
        return ['x' => 0, 'y' => 0];
    }

    /**
     * Get all 8 neighbor positions
     */
    protected function getNeighborPositions(int $x, int $y): array
    {
        return [
            ['x' => $x - 1, 'y' => $y - 1], // Top-left
            ['x' => $x,     'y' => $y - 1], // Top
            ['x' => $x + 1, 'y' => $y - 1], // Top-right
            ['x' => $x - 1, 'y' => $y],     // Left
            ['x' => $x + 1, 'y' => $y],     // Right
            ['x' => $x - 1, 'y' => $y + 1], // Bottom-left
            ['x' => $x,     'y' => $y + 1], // Bottom
            ['x' => $x + 1, 'y' => $y + 1], // Bottom-right
        ];
    }

    /**
     * Calculate Manhattan distance between two points
     */
    protected function calculateDistance(int $x1, int $y1, int $x2, int $y2): float
    {
        return sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2));
    }

    /**
     * Check if position is valid for building
     */
    public function isValidPosition(int $x, int $y): bool
    {
        // Check if cell is occupied
        if (WorldStructure::where('grid_x', $x)->where('grid_y', $y)->exists()) {
            return false;
        }

        // Check if in unlocked zone
        if (!$this->zoneService->isPositionInUnlockedZone($x, $y)) {
            return false;
        }

        // Check if adjacent to existing structure
        if (!$this->isAdjacentToStructure($x, $y)) {
            return false;
        }

        return true;
    }
}
