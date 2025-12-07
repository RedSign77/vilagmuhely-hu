<?php

namespace Webtechsolutions\ContentEngine\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWorldResource extends Model
{
    protected $fillable = [
        'user_id',
        'stone',
        'wood',
        'crystal_shards',
        'magic_essence',
        'total_structures_built',
        'total_upgrades_done',
        'last_resource_claim',
    ];

    protected $casts = [
        'stone' => 'integer',
        'wood' => 'integer',
        'crystal_shards' => 'integer',
        'magic_essence' => 'integer',
        'total_structures_built' => 'integer',
        'total_upgrades_done' => 'integer',
        'last_resource_claim' => 'datetime',
    ];

    /**
     * Get the user this resource belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Add resources
     */
    public function addResources(array $resources): void
    {
        foreach ($resources as $type => $amount) {
            if (in_array($type, ['stone', 'wood', 'crystal_shards', 'magic_essence'])) {
                $this->increment($type, $amount);
            }
        }

        $this->update(['last_resource_claim' => now()]);
    }

    /**
     * Spend resources
     */
    public function spendResources(array $costs): bool
    {
        // Check if user has enough resources
        foreach ($costs as $type => $amount) {
            if ($this->$type < $amount) {
                return false;
            }
        }

        // Deduct resources
        foreach ($costs as $type => $amount) {
            $this->decrement($type, $amount);
        }

        return true;
    }

    /**
     * Check if user can afford costs
     */
    public function canAfford(array $costs): bool
    {
        foreach ($costs as $type => $amount) {
            if (!isset($this->$type) || $this->$type < $amount) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get total resource count
     */
    public function getTotalResourcesAttribute(): int
    {
        return $this->stone + $this->wood + $this->crystal_shards + $this->magic_essence;
    }

    /**
     * Get resources as array
     */
    public function toArray(): array
    {
        return [
            'stone' => $this->stone,
            'wood' => $this->wood,
            'crystal_shards' => $this->crystal_shards,
            'magic_essence' => $this->magic_essence,
            'total_structures_built' => $this->total_structures_built,
            'total_upgrades_done' => $this->total_upgrades_done,
        ];
    }
}
