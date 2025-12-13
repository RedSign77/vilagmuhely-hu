<?php

use App\Http\Controllers\WorldController;
use App\Models\WorldElementInstance;
use App\Models\WorldMapConfig;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $mapConfig = WorldMapConfig::getInstance();

    $worldStats = [
        'total_elements' => WorldElementInstance::count(),
        'map_size' => "{$mapConfig->map_width}×{$mapConfig->map_height}",
        'biomes' => 5, // forest, meadow, desert, tundra, swamp
    ];

    return view('welcome', compact('worldStats'));
});

// World Routes
Route::get('/world', [WorldController::class, 'index'])->name('world.index');
