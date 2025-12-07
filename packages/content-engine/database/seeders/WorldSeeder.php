<?php

namespace Webtechsolutions\ContentEngine\Database\Seeders;

use Illuminate\Database\Seeder;
use Webtechsolutions\ContentEngine\Models\WorldStructure;
use Webtechsolutions\ContentEngine\Models\WorldZone;

class WorldSeeder extends Seeder
{
    /**
     * Seed the world zones and origin monument
     */
    public function run(): void
    {
        // Create zones
        $zones = [
            [
                'zone_key' => 'central',
                'name' => 'Origin Valley',
                'zone_type' => 'origin',
                'min_x' => -25,
                'max_x' => 25,
                'min_y' => -25,
                'max_y' => 25,
                'unlock_at' => 0,
                'color' => '#4ade80',
                'is_unlocked' => true,
            ],
            [
                'zone_key' => 'east',
                'name' => 'Crystal Plains',
                'zone_type' => 'creative',
                'min_x' => 26,
                'max_x' => 75,
                'min_y' => -25,
                'max_y' => 25,
                'unlock_at' => 100,
                'color' => '#60a5fa',
                'is_unlocked' => false,
            ],
            [
                'zone_key' => 'west',
                'name' => 'Makers Marsh',
                'zone_type' => 'makers',
                'min_x' => -75,
                'max_x' => -26,
                'min_y' => -25,
                'max_y' => 25,
                'unlock_at' => 250,
                'color' => '#a78bfa',
                'is_unlocked' => false,
            ],
            [
                'zone_key' => 'north',
                'name' => 'Knowledge Heights',
                'zone_type' => 'knowledge',
                'min_x' => -25,
                'max_x' => 25,
                'min_y' => 26,
                'max_y' => 75,
                'unlock_at' => 500,
                'color' => '#fbbf24',
                'is_unlocked' => false,
            ],
            [
                'zone_key' => 'south',
                'name' => 'Story Depths',
                'zone_type' => 'stories',
                'min_x' => -25,
                'max_x' => 25,
                'min_y' => -75,
                'max_y' => -26,
                'unlock_at' => 1000,
                'color' => '#f87171',
                'is_unlocked' => false,
            ],
        ];

        foreach ($zones as $zone) {
            WorldZone::updateOrCreate(
                ['zone_key' => $zone['zone_key']],
                $zone
            );
        }

        $this->command->info('Created 5 world zones');

        // Create Origin Monument if it doesn't exist
        if (!WorldStructure::ofType(WorldStructure::TYPE_ORIGIN)->exists()) {
            // Get the first user or create a system user
            $user = \App\Models\User::first();

            if (!$user) {
                $this->command->warn('No users found. Please create a user first.');
                return;
            }

            WorldStructure::create([
                'user_id' => $user->id,
                'structure_type' => WorldStructure::TYPE_ORIGIN,
                'grid_x' => 0,
                'grid_y' => 0,
                'level' => 1,
                'health' => 100,
                'decay_state' => WorldStructure::DECAY_ACTIVE,
                'placed_at' => now(),
                'last_owner_activity' => now(),
            ]);

            $this->command->info('Created Origin Monument at (0, 0)');
        } else {
            $this->command->info('Origin Monument already exists');
        }
    }
}
