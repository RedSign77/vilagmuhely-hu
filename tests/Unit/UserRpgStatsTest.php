<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserCrystalMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRpgStatsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_rpg_stats_correctly()
    {
        $user = User::factory()->create();

        $metric = UserCrystalMetric::factory()->create([
            'user_id' => $user->id,
            'facet_count' => 25,
            'glow_intensity' => 0.75,
            'purity_level' => 0.60,
        ]);

        $stats = $user->fresh()->rpg_stats;

        $this->assertEquals('Craftsperson', $stats['rank']);
        $this->assertEquals(25, $stats['level']);
        $this->assertEquals(75, $stats['aura']);
        $this->assertEquals(60, $stats['essence']);
    }

    /** @test */
    public function it_handles_users_without_metrics()
    {
        $user = User::factory()->create();

        $stats = $user->rpg_stats;

        $this->assertEquals('Novice', $stats['rank']);
        $this->assertEquals(1, $stats['level']);
        $this->assertEquals(0, $stats['aura']);
        $this->assertEquals(0, $stats['essence']);
    }

    /** @test */
    public function it_calculates_correct_ranks_at_different_levels()
    {
        $user = User::factory()->create();

        // Test Novice (level 1-7)
        UserCrystalMetric::factory()->create(['user_id' => $user->id, 'facet_count' => 5]);
        $this->assertEquals('Novice', $user->fresh()->rpg_stats['rank']);

        // Test Journeyman (level 8-14)
        $user->crystalMetric->update(['facet_count' => 10]);
        $this->assertEquals('Journeyman', $user->fresh()->rpg_stats['rank']);

        // Test Apprentice (level 15-24)
        $user->crystalMetric->update(['facet_count' => 20]);
        $this->assertEquals('Apprentice', $user->fresh()->rpg_stats['rank']);

        // Test Craftsperson (level 25-34)
        $user->crystalMetric->update(['facet_count' => 30]);
        $this->assertEquals('Craftsperson', $user->fresh()->rpg_stats['rank']);

        // Test Artisan (level 35-44)
        $user->crystalMetric->update(['facet_count' => 40]);
        $this->assertEquals('Artisan', $user->fresh()->rpg_stats['rank']);

        // Test Crystal Master (level 45-50)
        $user->crystalMetric->update(['facet_count' => 50]);
        $this->assertEquals('Crystal Master', $user->fresh()->rpg_stats['rank']);
    }

    /** @test */
    public function it_converts_percentages_correctly()
    {
        $user = User::factory()->create();

        UserCrystalMetric::factory()->create([
            'user_id' => $user->id,
            'glow_intensity' => 0.5,
            'purity_level' => 1.0,
        ]);

        $stats = $user->fresh()->rpg_stats;

        $this->assertEquals(50, $stats['aura']); // 0.5 * 100
        $this->assertEquals(100, $stats['essence']); // 1.0 * 100
    }

    /** @test */
    public function it_returns_correct_color_name()
    {
        $user = User::factory()->create();

        // Test with no metrics
        $this->assertEquals('Gray', $user->crystal_color_name);

        // Test with dominant colors
        UserCrystalMetric::factory()->create([
            'user_id' => $user->id,
            'dominant_colors' => ['#ff0000'], // Red
        ]);

        $this->assertEquals('Red', $user->fresh()->crystal_color_name);
    }
}
