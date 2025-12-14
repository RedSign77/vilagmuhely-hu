<?php

use App\Http\Controllers\CrystalGalleryController;
use App\Models\User;
use App\Models\UserCrystalMetric;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Get top 3 crystals by interaction score
    $topCrystals = UserCrystalMetric::with('user')
        ->topInteraction(3)
        ->get();

    // Get total users count
    $stats = [
        'total_users' => User::count(),
    ];

    return view('welcome', [
        'topCrystals' => $topCrystals,
        'stats' => $stats,
    ]);
});

// Crystal Gallery Routes
Route::get('/crystals', [CrystalGalleryController::class, 'index'])->name('crystals.gallery');
Route::get('/crystals/{user}', [CrystalGalleryController::class, 'show'])->name('crystals.show');
