<?php

namespace App\Http\Controllers;

use App\Models\WorldElementInstance;
use App\Models\WorldMapConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Webtechsolutions\ContentEngine\Models\WorldActivityLog;
use Webtechsolutions\ContentEngine\Services\WorldResourceService;

class WorldController extends Controller
{
    protected WorldResourceService $resourceService;

    public function __construct(WorldResourceService $resourceService)
    {
        $this->resourceService = $resourceService;
    }

    /**
     * Show the world map (2D top-down view)
     */
    public function index()
    {
        // Get map configuration
        $mapConfig = WorldMapConfig::getInstance();

        // Get total elements count
        $totalElements = WorldElementInstance::count();

        // Get user resources and discoveries (if authenticated)
        $userResources = null;
        $userDiscoveries = 0;

        if (Auth::check()) {
            $userResources = $this->resourceService->getResourceSummary(Auth::user());

            // Count unique elements user has discovered
            $userDiscoveries = WorldActivityLog::query()
                ->where('user_id', Auth::id())
                ->whereIn('activity_type', ['element_discovered', 'element_harvested'])
                ->whereNotNull('structure_id') // reusing structure_id column for element_id
                ->distinct('structure_id')
                ->count('structure_id');
        }

        return view('world.index', compact('mapConfig', 'totalElements', 'userResources', 'userDiscoveries'));
    }
}
