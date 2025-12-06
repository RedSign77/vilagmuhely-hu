<?php

use App\Http\Controllers\CrystalGalleryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Crystal Gallery Routes
Route::get('/crystals/gallery', [CrystalGalleryController::class, 'index'])->name('crystals.gallery');
Route::get('/crystals/{user}', [CrystalGalleryController::class, 'show'])->name('crystals.show');
