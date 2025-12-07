<?php

use App\Http\Controllers\WorldController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// World Routes
Route::get('/world', [WorldController::class, 'index'])->name('world.index');
Route::middleware(['auth'])->group(function () {
    Route::get('/my-structures', [WorldController::class, 'myStructures'])->name('world.my-structures');
});
