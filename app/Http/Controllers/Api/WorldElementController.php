<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorldElementInstance;
use App\Models\WorldMapConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Webtechsolutions\ContentEngine\Models\UserWorldResource;
use Webtechsolutions\ContentEngine\Models\WorldActivityLog;
use Webtechsolutions\ContentEngine\Services\WorldResourceService;

class WorldElementController extends Controller
{
    protected WorldResourceService $resourceService;

    public function __construct(WorldResourceService $resourceService)
    {
        $this->resourceService = $resourceService;
    }

    /**
     * Get map elements within viewport bounds (chunked loading)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getMap(Request $request): JsonResponse
    {
        $request->validate([
            'minX' => 'required|integer',
            'maxX' => 'required|integer',
            'minY' => 'required|integer',
            'maxY' => 'required|integer',
        ]);

        $minX = $request->integer('minX');
        $maxX = $request->integer('maxX');
        $minY = $request->integer('minY');
        $maxY = $request->integer('maxY');

        // Get elements in viewport
        $elements = WorldElementInstance::query()
            ->with('type:id,name,slug,category,rarity,image_path,max_width,max_height')
            ->inViewport($minX, $maxX, $minY, $maxY)
            ->get()
            ->map(function ($element) {
                return [
                    'id' => $element->id,
                    'position_x' => $element->position_x,
                    'position_y' => $element->position_y,
                    'rotation' => (float) $element->rotation,
                    'scale' => (float) $element->scale,
                    'biome' => $element->biome,
                    'is_interactable' => $element->is_interactable,
                    'interaction_count' => $element->interaction_count,
                    'type' => [
                        'id' => $element->type->id,
                        'name' => $element->type->name,
                        'slug' => $element->type->slug,
                        'category' => $element->type->category,
                        'rarity' => $element->type->rarity,
                        'image_path' => $element->type->image_url,
                        'max_width' => $element->type->max_width,
                        'max_height' => $element->type->max_height,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'viewport' => [
                'minX' => $minX,
                'maxX' => $maxX,
                'minY' => $minY,
                'maxY' => $maxY,
            ],
            'count' => $elements->count(),
            'elements' => $elements,
        ]);
    }

    /**
     * Get map configuration (bounds, settings, etc.)
     *
     * @return JsonResponse
     */
    public function getMapConfig(): JsonResponse
    {
        $config = WorldMapConfig::getInstance();
        $bounds = $config->getBounds();

        return response()->json([
            'success' => true,
            'config' => [
                'map_width' => $config->map_width,
                'map_height' => $config->map_height,
                'tile_size' => $config->tile_size,
                'default_biome' => $config->default_biome,
                'bounds' => $bounds,
                'total_area' => $config->area,
                'last_regenerated_at' => $config->last_regenerated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get single element details
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getElement(int $id): JsonResponse
    {
        $element = WorldElementInstance::with('type')->find($id);

        if (!$element) {
            return response()->json([
                'success' => false,
                'message' => 'Element not found',
            ], 404);
        }

        $hasResourceBonus = $element->type->hasResourceBonus();
        $resourceBonus = $hasResourceBonus ? $element->type->getResourceBonus() : null;

        // Check if user can claim bonus (if authenticated)
        $canClaim = false;
        $nextClaimAt = null;

        if (Auth::check() && $hasResourceBonus) {
            $canClaim = $this->canUserClaimBonus(Auth::user(), $element);

            if (!$canClaim && $element->type->getBonusType() === 'repeating') {
                $lastClaim = $this->getLastClaimTime(Auth::user(), $element);
                if ($lastClaim) {
                    $cooldownHours = $element->type->getCooldownHours();
                    $nextClaimAt = $lastClaim->addHours($cooldownHours)->toIso8601String();
                }
            }
        }

        return response()->json([
            'success' => true,
            'element' => [
                'id' => $element->id,
                'position_x' => $element->position_x,
                'position_y' => $element->position_y,
                'rotation' => (float) $element->rotation,
                'scale' => (float) $element->scale,
                'variant' => $element->variant,
                'biome' => $element->biome,
                'is_interactable' => $element->is_interactable,
                'interaction_count' => $element->interaction_count,
                'type' => [
                    'id' => $element->type->id,
                    'name' => $element->type->name,
                    'slug' => $element->type->slug,
                    'category' => $element->type->category,
                    'description' => $element->type->description,
                    'rarity' => $element->type->rarity,
                    'image_path' => $element->type->image_url,
                    'max_width' => $element->type->max_width,
                    'max_height' => $element->type->max_height,
                ],
                'resource_bonus' => $hasResourceBonus ? [
                    'resources' => $resourceBonus['resources'] ?? [],
                    'bonus_type' => $element->type->getBonusType(),
                    'cooldown_hours' => $element->type->getCooldownHours(),
                    'can_claim' => $canClaim,
                    'next_claim_at' => $nextClaimAt,
                ] : null,
            ],
        ]);
    }

    /**
     * Interact with element (claim resource bonus)
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function interact(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $element = WorldElementInstance::with('type')->find($id);

        if (!$element) {
            return response()->json([
                'success' => false,
                'message' => 'Element not found',
            ], 404);
        }

        if (!$element->is_interactable) {
            return response()->json([
                'success' => false,
                'message' => 'This element is not interactable',
            ], 403);
        }

        if (!$element->type->hasResourceBonus()) {
            return response()->json([
                'success' => false,
                'message' => 'This element has no resource bonus',
            ], 403);
        }

        // Check if user can claim bonus
        if (!$this->canUserClaimBonus($user, $element)) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot claim this bonus yet (cooldown or already claimed)',
            ], 403);
        }

        // Award resources
        $resourceBonus = $element->type->getResourceBonus();
        $resources = $resourceBonus['resources'] ?? [];

        if (empty($resources)) {
            return response()->json([
                'success' => false,
                'message' => 'No resources configured for this element',
            ], 500);
        }

        // Add resources to user
        $userResources = $this->resourceService->getResources($user);
        $userResources->addResources($resources);

        // Increment interaction count
        $element->incrementInteraction();

        // Log activity
        WorldActivityLog::log(
            $user->id,
            $element->type->getBonusType() === 'repeating' ? 'element_harvested' : 'element_discovered',
            $element->id,
            [
                'element_type' => $element->type->name,
                'resources_awarded' => $resources,
                'position' => ['x' => $element->position_x, 'y' => $element->position_y],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Resources claimed successfully!',
            'resources_awarded' => $resources,
            'updated_resources' => [
                'stone' => $userResources->stone,
                'wood' => $userResources->wood,
                'crystal_shards' => $userResources->crystal_shards,
                'magic_essence' => $userResources->magic_essence,
                'total_structures_built' => $userResources->total_structures_built,
            ],
        ]);
    }

    /**
     * Get user's discovered elements (elements they've interacted with)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDiscoveries(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        // Get all element interactions for this user
        $discoveries = WorldActivityLog::query()
            ->where('user_id', $user->id)
            ->whereIn('activity_type', ['element_discovered', 'element_harvested'])
            ->whereNotNull('structure_id') // reusing structure_id column for element_id
            ->with('structure.type:id,name,category,rarity')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'element_id' => $log->structure_id,
                    'element_name' => $log->structure?->type->name ?? 'Unknown',
                    'category' => $log->structure?->type->category ?? 'unknown',
                    'rarity' => $log->structure?->type->rarity ?? 'common',
                    'discovered_at' => $log->created_at->toIso8601String(),
                    'resources_awarded' => $log->details['resources_awarded'] ?? [],
                ];
            });

        return response()->json([
            'success' => true,
            'total_discoveries' => $discoveries->count(),
            'unique_elements' => $discoveries->pluck('element_id')->unique()->count(),
            'discoveries' => $discoveries,
        ]);
    }

    /**
     * Check if user can claim bonus from this element
     *
     * @param $user
     * @param WorldElementInstance $element
     * @return bool
     */
    protected function canUserClaimBonus($user, WorldElementInstance $element): bool
    {
        $bonusType = $element->type->getBonusType();

        // For one-time bonuses, check if user has ever claimed
        if ($bonusType === 'one_time') {
            $hasClaimed = WorldActivityLog::query()
                ->where('user_id', $user->id)
                ->where('structure_id', $element->id)
                ->where('activity_type', 'element_discovered')
                ->exists();

            return !$hasClaimed;
        }

        // For repeating bonuses, check cooldown
        $lastClaim = $this->getLastClaimTime($user, $element);

        if (!$lastClaim) {
            return true; // Never claimed before
        }

        $cooldownHours = $element->type->getCooldownHours();
        $canClaimAt = $lastClaim->addHours($cooldownHours);

        return now()->greaterThanOrEqualTo($canClaimAt);
    }

    /**
     * Get last claim time for repeating bonuses
     *
     * @param $user
     * @param WorldElementInstance $element
     * @return \Carbon\Carbon|null
     */
    protected function getLastClaimTime($user, WorldElementInstance $element)
    {
        $lastLog = WorldActivityLog::query()
            ->where('user_id', $user->id)
            ->where('structure_id', $element->id)
            ->where('activity_type', 'element_harvested')
            ->orderBy('created_at', 'desc')
            ->first();

        return $lastLog?->created_at;
    }
}
