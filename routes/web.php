<?php

use App\Http\Controllers\WorldController;
use Illuminate\Support\Facades\Route;
use Webtechsolutions\ContentEngine\Models\WorldStructure;
use Webtechsolutions\ContentEngine\Services\ZoneService;

Route::get('/', function () {
    $zoneService = app(ZoneService::class);

    $worldStats = [
        'total_structures' => WorldStructure::count(),
        'total_builders' => WorldStructure::distinct('user_id')->count(),
        'unlocked_zones' => $zoneService->getUnlockedZones()->count(),
    ];

    $zoneProgress = $zoneService->getNextZoneProgress();

    return view('welcome', compact('worldStats', 'zoneProgress'));
});

// World Routes
Route::get('/world', [WorldController::class, 'index'])->name('world.index');
Route::middleware(['auth'])->group(function () {
    Route::get('/my-structures', [WorldController::class, 'myStructures'])->name('world.my-structures');
});
