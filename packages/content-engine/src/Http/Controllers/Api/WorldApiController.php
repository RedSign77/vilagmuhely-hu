<?php

namespace Webtechsolutions\ContentEngine\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webtechsolutions\ContentEngine\Models\WorldStructure;
use Webtechsolutions\ContentEngine\Services\AdjacencyService;
use Webtechsolutions\ContentEngine\Services\WorldBuilderService;
use Webtechsolutions\ContentEngine\Services\WorldResourceService;
use Webtechsolutions\ContentEngine\Services\ZoneService;

class WorldApiController extends Controller
{
    protected WorldResourceService $resourceService;
    protected WorldBuilderService $builderService;
    protected AdjacencyService $adjacencyService;
    protected ZoneService $zoneService;

    public function __construct(
        WorldResourceService $resourceService,
        WorldBuilderService $builderService,
        AdjacencyService $adjacencyService,
        ZoneService $zoneService
    ) {
        $this->resourceService = $resourceService;
        $this->builderService = $builderService;
        $this->adjacencyService = $adjacencyService;
        $this->zoneService = $zoneService;
    }

    /**
     * Get world map data (supports chunking)
     */
    public function getMap(Request $request): JsonResponse
    {
        $chunkX = (int) $request->get('chunk_x', 0);
        $chunkY = (int) $request->get('chunk_y', 0);
        $size = min(50, (int) $request->get('size', 20));

        $minX = $chunkX;
        $maxX = $chunkX + $size;
        $minY = $chunkY;
        $maxY = $chunkY + $size;

        $structures = WorldStructure::inArea($minX, $maxX, $minY, $maxY)
            ->with('user:id,name,avatar')
            ->active()
            ->get()
            ->map(function ($structure) {
                return [
                    'id' => $structure->id,
                    'type' => $structure->structure_type,
                    'type_name' => $structure->type_name,
                    'name' => $structure->display_name,
                    'description' => $structure->display_description,
                    'x' => $structure->grid_x,
                    'y' => $structure->grid_y,
                    'level' => $structure->level,
                    'user_id' => $structure->user_id,
                    'user_name' => $structure->user->name,
                    'color' => $structure->primary_color,
                    'decay_state' => $structure->decay_state,
                    'customization' => $structure->customization,
                ];
            });

        $zones = $this->zoneService->getAllZonesWithStatus();

        return response()->json([
            'success' => true,
            'data' => [
                'chunk' => [
                    'x' => $chunkX,
                    'y' => $chunkY,
                    'width' => $size,
                    'height' => $size,
                ],
                'structures' => $structures,
                'zones' => $zones,
            ],
        ]);
    }

    /**
     * Get world statistics
     */
    public function getStats(): JsonResponse
    {
        $totalStructures = WorldStructure::count();
        $activeStructures = WorldStructure::active()->count();
        $totalUsers = WorldStructure::distinct('user_id')->count();

        $typeDistribution = WorldStructure::active()
            ->selectRaw('structure_type, count(*) as count')
            ->groupBy('structure_type')
            ->pluck('count', 'structure_type');

        $zoneProgress = $this->zoneService->getNextZoneProgress();

        return response()->json([
            'success' => true,
            'data' => [
                'total_structures' => $totalStructures,
                'active_structures' => $activeStructures,
                'total_builders' => $totalUsers,
                'type_distribution' => $typeDistribution,
                'zone_progress' => $zoneProgress,
            ],
        ]);
    }

    /**
     * Get world leaderboard
     */
    public function getLeaderboard(Request $request): JsonResponse
    {
        $category = $request->get('category', 'structures');
        $limit = min(20, (int) $request->get('limit', 10));

        $leaderboard = match ($category) {
            'resources' => $this->getResourceLeaderboard($limit),
            'upgrades' => $this->getUpgradeLeaderboard($limit),
            default => $this->getStructureLeaderboard($limit),
        };

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
        ]);
    }

    /**
     * Get specific structure details
     */
    public function getStructure(int $id): JsonResponse
    {
        $structure = WorldStructure::with('user:id,name,avatar')->find($id);

        if (!$structure) {
            return response()->json([
                'success' => false,
                'message' => 'Structure not found',
            ], 404);
        }

        $nearbyStructures = $this->builderService->getNearbyStructures(
            $structure->grid_x,
            $structure->grid_y,
            3
        );

        return response()->json([
            'success' => true,
            'data' => [
                'structure' => [
                    'id' => $structure->id,
                    'type' => $structure->structure_type,
                    'type_name' => $structure->type_name,
                    'position' => ['x' => $structure->grid_x, 'y' => $structure->grid_y],
                    'level' => $structure->level,
                    'health' => $structure->health,
                    'decay_state' => $structure->decay_state,
                    'placed_at' => $structure->placed_at->toIso8601String(),
                    'color' => $structure->color,
                ],
                'owner' => [
                    'id' => $structure->user->id,
                    'name' => $structure->user->name,
                    'avatar' => $structure->user->avatar,
                ],
                'nearby_count' => $nearbyStructures->count(),
            ],
        ]);
    }

    /**
     * Get user's resources (authenticated)
     */
    public function getMyResources(Request $request): JsonResponse
    {
        $user = $request->user();
        $summary = $this->resourceService->getResourceSummary($user);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get user's structures (authenticated)
     */
    public function getMyStructures(Request $request): JsonResponse
    {
        $user = $request->user();

        $structures = WorldStructure::where('user_id', $user->id)
            ->active()
            ->orderBy('placed_at', 'desc')
            ->get()
            ->map(function ($structure) {
                return [
                    'id' => $structure->id,
                    'type' => $structure->structure_type,
                    'type_name' => $structure->type_name,
                    'position' => ['x' => $structure->grid_x, 'y' => $structure->grid_y],
                    'level' => $structure->level,
                    'health' => $structure->health,
                    'placed_at' => $structure->placed_at->toIso8601String(),
                    'color' => $structure->color,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $structures,
        ]);
    }

    /**
     * Place new structure (authenticated)
     */
    public function build(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'x' => 'required|integer',
            'y' => 'required|integer',
            'metadata' => 'nullable|array',
            'customization' => 'nullable|array',
            'customization.name' => 'nullable|string|max:30',
            'customization.description' => 'nullable|string|max:200',
            'customization.colors' => 'nullable|array',
            'customization.colors.primary' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'customization.style' => 'nullable|array',
            'customization.features' => 'nullable|array',
        ]);

        try {
            $user = $request->user();
            $structure = $this->builderService->placeStructure(
                $user,
                $validated['type'],
                $validated['x'],
                $validated['y'],
                $validated['metadata'] ?? null,
                $validated['customization'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Structure placed successfully',
                'data' => [
                    'structure' => [
                        'id' => $structure->id,
                        'type' => $structure->structure_type,
                        'name' => $structure->display_name,
                        'position' => ['x' => $structure->grid_x, 'y' => $structure->grid_y],
                        'level' => $structure->level,
                        'color' => $structure->primary_color,
                        'customization' => $structure->customization,
                    ],
                    'resources' => $this->resourceService->getResourceSummary($user),
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to place structure',
            ], 500);
        }
    }

    /**
     * Upgrade structure (authenticated)
     */
    public function upgrade(Request $request, int $structureId): JsonResponse
    {
        $structure = WorldStructure::find($structureId);

        if (!$structure) {
            return response()->json([
                'success' => false,
                'message' => 'Structure not found',
            ], 404);
        }

        if ($structure->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $upgraded = $this->builderService->upgradeStructure($structure);

            return response()->json([
                'success' => true,
                'message' => 'Structure upgraded successfully',
                'data' => [
                    'structure' => [
                        'id' => $upgraded->id,
                        'type' => $upgraded->structure_type,
                        'level' => $upgraded->level,
                    ],
                    'resources' => $this->resourceService->getResourceSummary($request->user()),
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upgrade structure',
            ], 500);
        }
    }

    /**
     * Get suggested build positions (authenticated)
     */
    public function suggestPositions(Request $request): JsonResponse
    {
        $user = $request->user();
        $structureType = $request->get('type', 'cottage');
        $limit = min(10, (int) $request->get('limit', 5));

        $suggestions = $this->builderService->suggestPositions($user, $structureType, $limit);

        return response()->json([
            'success' => true,
            'data' => $suggestions,
        ]);
    }

    /**
     * Get available customization options for a structure type
     */
    public function getCustomizationOptions(string $type): JsonResponse
    {
        $defaults = WorldStructure::getDefaultCustomization($type);

        return response()->json([
            'success' => true,
            'data' => [
                'type' => $type,
                'defaults' => $defaults,
                'structure_types' => [
                    WorldStructure::TYPE_COTTAGE => 'Cottage',
                    WorldStructure::TYPE_WORKSHOP => 'Workshop',
                    WorldStructure::TYPE_GALLERY => 'Gallery',
                    WorldStructure::TYPE_LIBRARY => 'Library',
                    WorldStructure::TYPE_ACADEMY => 'Academy',
                    WorldStructure::TYPE_TOWER => 'Tower',
                    WorldStructure::TYPE_MONUMENT => 'Monument',
                    WorldStructure::TYPE_GARDEN => 'Garden',
                ],
            ],
        ]);
    }

    /**
     * Helper: Get structure leaderboard
     */
    protected function getStructureLeaderboard(int $limit): array
    {
        return User::withCount(['contents' => function ($query) {
            $query->where('user_id', WorldStructure::select('user_id'));
        }])
            ->join('world_structures', 'users.id', '=', 'world_structures.user_id')
            ->selectRaw('users.id, users.name, users.avatar, count(world_structures.id) as structure_count')
            ->groupBy('users.id', 'users.name', 'users.avatar')
            ->orderBy('structure_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'count' => $user->structure_count,
                ];
            })
            ->toArray();
    }

    /**
     * Helper: Get resource leaderboard
     */
    protected function getResourceLeaderboard(int $limit): array
    {
        return User::join('user_world_resources', 'users.id', '=', 'user_world_resources.user_id')
            ->selectRaw('users.id, users.name, users.avatar, (stone + wood + crystal_shards + magic_essence) as total_resources')
            ->orderBy('total_resources', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'total_resources' => $user->total_resources,
                ];
            })
            ->toArray();
    }

    /**
     * Helper: Get upgrade leaderboard
     */
    protected function getUpgradeLeaderboard(int $limit): array
    {
        return User::join('user_world_resources', 'users.id', '=', 'user_world_resources.user_id')
            ->select('users.id', 'users.name', 'users.avatar', 'user_world_resources.total_upgrades_done')
            ->orderBy('total_upgrades_done', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'upgrades' => $user->total_upgrades_done,
                ];
            })
            ->toArray();
    }
}
