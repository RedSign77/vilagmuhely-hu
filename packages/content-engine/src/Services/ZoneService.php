<?php

namespace Webtechsolutions\ContentEngine\Services;

use Illuminate\Support\Collection;
use Webtechsolutions\ContentEngine\Models\WorldStructure;
use Webtechsolutions\ContentEngine\Models\WorldZone;

class ZoneService
{
    /**
     * Get all unlocked zones based on total structure count
     */
    public function getUnlockedZones(): Collection
    {
        $totalStructures = WorldStructure::count();

        // Update zone unlock status based on total structures
        WorldZone::where('unlock_at', '<=', $totalStructures)
            ->where('is_unlocked', false)
            ->update(['is_unlocked' => true]);

        return WorldZone::unlocked()->get();
    }

    /**
     * Check if position is in unlocked zone
     */
    public function isPositionInUnlockedZone(int $x, int $y): bool
    {
        $unlockedZones = $this->getUnlockedZones();

        foreach ($unlockedZones as $zone) {
            if ($zone->containsPosition($x, $y)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get zone info for position
     */
    public function getZoneAt(int $x, int $y): ?WorldZone
    {
        $zones = WorldZone::all();

        foreach ($zones as $zone) {
            if ($zone->containsPosition($x, $y)) {
                return $zone;
            }
        }

        return null;
    }

    /**
     * Calculate progress to next zone unlock
     */
    public function getNextZoneProgress(): array
    {
        $totalStructures = WorldStructure::count();
        $nextZone = WorldZone::locked()
            ->orderBy('unlock_at')
            ->first();

        if (!$nextZone) {
            return [
                'all_unlocked' => true,
                'total_structures' => $totalStructures,
            ];
        }

        return [
            'all_unlocked' => false,
            'current_structures' => $totalStructures,
            'required_structures' => $nextZone->unlock_at,
            'remaining' => max(0, $nextZone->unlock_at - $totalStructures),
            'progress_percentage' => min(100, ($totalStructures / $nextZone->unlock_at) * 100),
            'next_zone' => [
                'name' => $nextZone->name,
                'type' => $nextZone->zone_type,
                'color' => $nextZone->color,
            ],
        ];
    }

    /**
     * Get all zones with unlock status
     */
    public function getAllZonesWithStatus(): Collection
    {
        $totalStructures = WorldStructure::count();

        return WorldZone::all()->map(function ($zone) use ($totalStructures) {
            return [
                'id' => $zone->id,
                'key' => $zone->zone_key,
                'name' => $zone->name,
                'type' => $zone->zone_type,
                'bounds' => [
                    'min_x' => $zone->min_x,
                    'max_x' => $zone->max_x,
                    'min_y' => $zone->min_y,
                    'max_y' => $zone->max_y,
                ],
                'center' => $zone->center,
                'color' => $zone->color,
                'is_unlocked' => $zone->is_unlocked,
                'unlock_at' => $zone->unlock_at,
                'progress' => $zone->unlock_at > 0
                    ? min(100, ($totalStructures / $zone->unlock_at) * 100)
                    : 100,
            ];
        });
    }
}
