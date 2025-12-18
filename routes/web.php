<?php

use App\Http\Controllers\ContentDownloadController;
use App\Http\Controllers\ContentLibraryController;
use App\Http\Controllers\CrystalGalleryController;
use App\Http\Controllers\InvitationController;
use App\Models\User;
use App\Models\UserCrystalMetric;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Get top 6 crystals by interaction score
    $topCrystals = UserCrystalMetric::with('user')
        ->topInteraction(6)
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

// Content Library Route
Route::get('/library', [ContentLibraryController::class, 'index'])->name('library.index');

// Content Download Route
Route::get('/content/{content}/download', [ContentDownloadController::class, 'download'])
    ->middleware('auth')
    ->name('content.download');

// Invitation Routes
Route::get('/invitations/{token}/accept', [InvitationController::class, 'accept'])
    ->name('invitations.accept');
