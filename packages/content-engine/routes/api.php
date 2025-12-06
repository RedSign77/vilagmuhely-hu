<?php

use Illuminate\Support\Facades\Route;
use Webtechsolutions\ContentEngine\Http\Controllers\Api\CrystalApiController;

Route::prefix('api/v1')->group(function () {
    // Public crystal endpoints
    Route::get('/crystals/{userId}', [CrystalApiController::class, 'show']);
    Route::get('/crystals/gallery', [CrystalApiController::class, 'gallery']);
    Route::get('/crystals/leaderboard', [CrystalApiController::class, 'leaderboard']);

    // Authenticated endpoints
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/content/{contentId}/rate', [CrystalApiController::class, 'rateContent']);
        Route::post('/content/{contentId}/rate/{ratingId}/helpful', [CrystalApiController::class, 'markRatingHelpful']);
    });
});
