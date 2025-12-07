<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Webtechsolutions\ContentEngine\Models\WorldStructure;
use Webtechsolutions\ContentEngine\Services\WorldResourceService;
use Webtechsolutions\ContentEngine\Services\ZoneService;

class WorldController extends Controller
{
    protected WorldResourceService $resourceService;
    protected ZoneService $zoneService;

    public function __construct(WorldResourceService $resourceService, ZoneService $zoneService)
    {
        $this->resourceService = $resourceService;
        $this->zoneService = $zoneService;
    }

    /**
     * Show the world map
     */
    public function index()
    {
        $totalStructures = WorldStructure::active()->count();
        $zoneProgress = $this->zoneService->getNextZoneProgress();

        $userResources = null;
        if (Auth::check()) {
            $userResources = $this->resourceService->getResourceSummary(Auth::user());
        }

        return view('world.index', compact('totalStructures', 'zoneProgress', 'userResources'));
    }

    /**
     * Show user's structures
     */
    public function myStructures(Request $request)
    {
        $user = $request->user();

        $structures = WorldStructure::where('user_id', $user->id)
            ->orderBy('placed_at', 'desc')
            ->paginate(20);

        $userResources = $this->resourceService->getResourceSummary($user);

        return view('world.my-structures', compact('structures', 'userResources'));
    }
}
