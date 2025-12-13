<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorldElementInstance;
use App\Models\WorldElementType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Webtechsolutions\ContentEngine\Models\UserWorldResource;

class WorldElementInteractionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations
        $this->artisan('migrate:fresh');
    }

    public function test_can_view_map_config(): void
    {
        $response = $this->getJson('/api/v1/world/map-config');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'config' => [
                    'map_width',
                    'map_height',
                    'tile_size',
                    'default_biome',
                    'bounds',
                ],
            ]);
    }

    public function test_can_get_map_elements_with_viewport(): void
    {
        // Create element type and instance
        $elementType = WorldElementType::create([
            'name' => 'Test Tree',
            'slug' => 'test-tree',
            'category' => 'vegetation',
            'rarity' => 'common',
            'density_weight' => 1.0,
            'max_width' => 64,
            'max_height' => 64,
            'is_active' => true,
        ]);

        WorldElementInstance::create([
            'world_element_type_id' => $elementType->id,
            'position_x' => 10,
            'position_y' => 10,
            'rotation' => 0,
            'scale' => 1.0,
        ]);

        $response = $this->getJson('/api/v1/world/map?minX=0&maxX=50&minY=0&maxY=50');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'elements' => [
                    '*' => [
                        'id',
                        'position_x',
                        'position_y',
                        'rotation',
                        'scale',
                        'type' => [
                            'name',
                            'category',
                            'rarity',
                        ],
                    ],
                ],
            ]);
    }

    public function test_can_get_element_details(): void
    {
        $elementType = WorldElementType::create([
            'name' => 'Oak Tree',
            'slug' => 'oak-tree',
            'category' => 'vegetation',
            'rarity' => 'common',
            'density_weight' => 1.0,
            'max_width' => 64,
            'max_height' => 64,
            'resource_bonus' => [
                'bonus_type' => 'one_time',
                'resources' => [
                    'wood' => 10,
                    'stone' => 5,
                ],
            ],
            'is_active' => true,
        ]);

        $element = WorldElementInstance::create([
            'world_element_type_id' => $elementType->id,
            'position_x' => 10,
            'position_y' => 10,
            'rotation' => 45,
            'scale' => 1.2,
        ]);

        $response = $this->getJson("/api/v1/world/element/{$element->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'element' => [
                    'id' => $element->id,
                    'position_x' => 10,
                    'position_y' => 10,
                    'rotation' => 45,
                    'scale' => 1.2,
                    'resource_bonus' => [
                        'bonus_type' => 'one_time',
                        'resources' => [
                            'wood' => 10,
                            'stone' => 5,
                        ],
                        'can_claim' => false, // Not authenticated
                    ],
                ],
            ]);
    }

    public function test_authenticated_user_can_claim_resource_bonus(): void
    {
        $user = User::factory()->create();

        $elementType = WorldElementType::create([
            'name' => 'Crystal Rock',
            'slug' => 'crystal-rock',
            'category' => 'terrain',
            'rarity' => 'rare',
            'density_weight' => 0.5,
            'max_width' => 64,
            'max_height' => 64,
            'resource_bonus' => [
                'bonus_type' => 'one_time',
                'resources' => [
                    'crystal_shards' => 15,
                    'stone' => 10,
                ],
            ],
            'is_active' => true,
        ]);

        $element = WorldElementInstance::create([
            'world_element_type_id' => $elementType->id,
            'position_x' => 20,
            'position_y' => 30,
            'rotation' => 0,
            'scale' => 1.0,
        ]);

        // Ensure user has resource records
        UserWorldResource::create([
            'user_id' => $user->id,
            'stone' => 0,
            'wood' => 0,
            'crystal_shards' => 0,
            'magic_essence' => 0,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/world/element/{$element->id}/interact");

        if ($response->status() !== 200) {
            dump($response->json());
        }

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'message',
                'resources_awarded',
                'updated_resources',
            ]);

        // Verify resources were awarded
        $userResources = UserWorldResource::where('user_id', $user->id)->first();
        $this->assertEquals(15, $userResources->crystal_shards);
        $this->assertEquals(10, $userResources->stone);

        // Verify interaction was logged
        $element->refresh();
        $this->assertEquals(1, $element->interaction_count);
    }

    public function test_cannot_claim_bonus_twice_for_one_time_bonus(): void
    {
        $user = User::factory()->create();

        $elementType = WorldElementType::create([
            'name' => 'Magic Flower',
            'slug' => 'magic-flower',
            'category' => 'vegetation',
            'rarity' => 'epic',
            'density_weight' => 0.2,
            'max_width' => 32,
            'max_height' => 32,
            'resource_bonus' => [
                'bonus_type' => 'one_time',
                'resources' => [
                    'magic_essence' => 20,
                ],
            ],
            'is_active' => true,
        ]);

        $element = WorldElementInstance::create([
            'world_element_type_id' => $elementType->id,
            'position_x' => 50,
            'position_y' => 50,
            'rotation' => 0,
            'scale' => 1.0,
        ]);

        // Ensure user has resource records
        UserWorldResource::create([
            'user_id' => $user->id,
            'stone' => 0,
            'wood' => 0,
            'crystal_shards' => 0,
            'magic_essence' => 0,
        ]);

        // First claim should succeed
        $response1 = $this->actingAs($user)
            ->postJson("/api/v1/world/element/{$element->id}/interact");

        $response1->assertStatus(200)
            ->assertJson(['success' => true]);

        // Second claim should fail
        $response2 = $this->actingAs($user)
            ->postJson("/api/v1/world/element/{$element->id}/interact");

        $response2->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You cannot claim this bonus yet (cooldown or already claimed)',
            ]);

        // Verify only one set of resources was awarded
        $userResources = UserWorldResource::where('user_id', $user->id)->first();
        $this->assertEquals(20, $userResources->magic_essence);
    }

    public function test_unauthenticated_user_cannot_claim_bonus(): void
    {
        $elementType = WorldElementType::create([
            'name' => 'Test Element',
            'slug' => 'test-element',
            'category' => 'decoration',
            'rarity' => 'common',
            'density_weight' => 1.0,
            'max_width' => 32,
            'max_height' => 32,
            'resource_bonus' => [
                'bonus_type' => 'one_time',
                'resources' => [
                    'stone' => 5,
                ],
            ],
            'is_active' => true,
        ]);

        $element = WorldElementInstance::create([
            'world_element_type_id' => $elementType->id,
            'position_x' => 0,
            'position_y' => 0,
            'rotation' => 0,
            'scale' => 1.0,
        ]);

        $response = $this->postJson("/api/v1/world/element/{$element->id}/interact");

        $response->assertStatus(401);
    }
}
