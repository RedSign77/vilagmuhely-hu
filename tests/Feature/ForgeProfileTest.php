<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserCrystalMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForgeProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_forge_profile_with_username()
    {
        $user = User::factory()->create(['username' => 'testcreator', 'name' => 'Test Creator']);
        UserCrystalMetric::factory()->create(['user_id' => $user->id, 'facet_count' => 25]);

        $response = $this->get('/forge/testcreator');

        $response->assertOk();
        $response->assertSee("Creator #{$user->id}"); // Anonymized name
        $response->assertSee('Craftsperson'); // Rank for level 25
    }

    /** @test */
    public function it_redirects_to_gallery_if_no_metrics()
    {
        $user = User::factory()->create(['username' => 'newuser']);

        $response = $this->get('/forge/newuser');

        $response->assertRedirect(route('crystals.gallery'));
        $response->assertSessionHas('info');
    }

    /** @test */
    public function it_shows_anonymized_name_for_privacy()
    {
        $user = User::factory()->create(['username' => 'testcreator', 'name' => 'Real Name']);
        UserCrystalMetric::factory()->create(['user_id' => $user->id]);

        $response = $this->get('/forge/testcreator');

        $response->assertOk();
        $response->assertSee("Creator #{$user->id}");
        $response->assertDontSee('Real Name');
    }

    /** @test */
    public function it_displays_rpg_stats_correctly()
    {
        $user = User::factory()->create(['username' => 'testcreator']);
        UserCrystalMetric::factory()->create([
            'user_id' => $user->id,
            'facet_count' => 35,
            'glow_intensity' => 0.75,
            'purity_level' => 0.60,
        ]);

        $response = $this->get('/forge/testcreator');

        $response->assertOk();
        $response->assertSee('Artisan'); // Rank for level 35
        $response->assertSee('75%'); // Aura
        $response->assertSee('60%'); // Essence
    }

    /** @test */
    public function it_displays_activity_feed()
    {
        $user = User::factory()->create(['username' => 'testcreator']);
        UserCrystalMetric::factory()->create(['user_id' => $user->id]);

        $response = $this->get('/forge/testcreator');

        $response->assertOk();
        $response->assertSee('The Forge Log');
        $response->assertSee('Recent milestones and activities');
    }
}
