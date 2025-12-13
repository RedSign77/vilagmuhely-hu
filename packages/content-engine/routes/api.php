<?php

use App\Http\Controllers\Api\WorldElementController;
use Illuminate\Support\Facades\Route;
use Webtechsolutions\ContentEngine\Http\Controllers\Api\WorldApiController;

Route::prefix('api/v1')->middleware(['web'])->group(function () {
    // === NEW: World Element API (2D Map System) ===
    // Public element endpoints
    Route::get('/world/map', [WorldElementController::class, 'getMap']);
    Route::get('/world/map-config', [WorldElementController::class, 'getMapConfig']);
    Route::get('/world/element/{id}', [WorldElementController::class, 'getElement']);

    // Authenticated element endpoints
    Route::middleware(['auth'])->group(function () {
        Route::post('/world/element/{id}/interact', [WorldElementController::class, 'interact']);
        Route::get('/world/my-discoveries', [WorldElementController::class, 'getDiscoveries']);
        Route::get('/world/my-resources', [WorldApiController::class, 'getMyResources']);
    });

    // === OLD: Structure-based system (deprecated, for migration compatibility) ===
    // Route::get('/world/stats', [WorldApiController::class, 'getStats']);
    // Route::get('/world/leaderboard', [WorldApiController::class, 'getLeaderboard']);
    // Route::get('/world/structure/{id}', [WorldApiController::class, 'getStructure']);
    // Route::get('/world/customization/{type}', [WorldApiController::class, 'getCustomizationOptions']);
    //
    // Route::middleware(['auth'])->group(function () {
    //     Route::get('/world/my-structures', [WorldApiController::class, 'getMyStructures']);
    //     Route::post('/world/build', [WorldApiController::class, 'build']);
    //     Route::post('/world/upgrade/{structure}', [WorldApiController::class, 'upgrade']);
    //     Route::get('/world/suggest-positions', [WorldApiController::class, 'suggestPositions']);
    // });
});
