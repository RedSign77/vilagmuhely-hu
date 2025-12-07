<?php

use Illuminate\Support\Facades\Route;
use Webtechsolutions\ContentEngine\Http\Controllers\Api\WorldApiController;

Route::prefix('api/v1')->group(function () {
    // Public world endpoints
    Route::get('/world/map', [WorldApiController::class, 'getMap']);
    Route::get('/world/stats', [WorldApiController::class, 'getStats']);
    Route::get('/world/leaderboard', [WorldApiController::class, 'getLeaderboard']);
    Route::get('/world/structure/{id}', [WorldApiController::class, 'getStructure']);

    // Authenticated world endpoints
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/world/my-resources', [WorldApiController::class, 'getMyResources']);
        Route::get('/world/my-structures', [WorldApiController::class, 'getMyStructures']);
        Route::post('/world/build', [WorldApiController::class, 'build']);
        Route::post('/world/upgrade/{structure}', [WorldApiController::class, 'upgrade']);
        Route::get('/world/suggest-positions', [WorldApiController::class, 'suggestPositions']);
    });
});
