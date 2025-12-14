<?php

use App\Http\Controllers\Api\CrystalApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public crystal endpoints
    Route::get('/crystals/gallery', [CrystalApiController::class, 'gallery']);
    Route::get('/crystals/leaderboard', [CrystalApiController::class, 'leaderboard']);
    Route::get('/crystals/{userId}', [CrystalApiController::class, 'show']);

    // Authenticated endpoints
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/content/{contentId}/rate', [CrystalApiController::class, 'rateContent']);
        Route::post('/content/{contentId}/rate/{ratingId}/helpful', [CrystalApiController::class, 'markRatingHelpful']);
    });
});
