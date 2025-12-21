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

    // Get featured content from library (3 most recent published items)
    $featuredContent = \Webtechsolutions\ContentEngine\Models\Content::query()
        ->public()
        ->published()
        ->with(['category', 'creator'])
        ->orderByDesc('published_at')
        ->limit(3)
        ->get();

    // Get total users count
    $stats = [
        'total_users' => User::count(),
    ];

    return view('welcome', [
        'topCrystals' => $topCrystals,
        'featuredContent' => $featuredContent,
        'stats' => $stats,
    ]);
});

// Crystal Gallery Routes
Route::get('/crystals', [CrystalGalleryController::class, 'index'])->name('crystals.gallery');
Route::get('/crystals/{user}', [CrystalGalleryController::class, 'show'])->name('crystals.show');

// Content Library Route
Route::get('/library', [ContentLibraryController::class, 'index'])->name('library.index');

// Changelog Route
Route::get('/changelog', [\App\Http\Controllers\ChangeLogController::class, 'index'])->name('changelog.index');

// Content Download Route
Route::get('/content/{content}/download', [ContentDownloadController::class, 'download'])
    ->middleware('auth')
    ->name('content.download');

// Invitation Routes
Route::get('/invitations/{token}/accept', [InvitationController::class, 'accept'])
    ->name('invitations.accept');

// Custom Email Verification Route (unauthenticated)
Route::get('/email-verification/{id}/{hash}', \App\Http\Controllers\Auth\EmailVerificationController::class)
    ->middleware(['throttle:6,1'])
    ->name('custom.email-verification.verify');

// Sitemap Route
Route::get('/sitemap.xml', function () {
    $sitemap = \Spatie\Sitemap\Sitemap::create();

    // Static pages
    $sitemap->add(\Spatie\Sitemap\Tags\Url::create('/')
        ->setLastModificationDate(now())
        ->setPriority(1.0)
        ->setChangeFrequency(\Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_DAILY));

    $sitemap->add(\Spatie\Sitemap\Tags\Url::create('/library')
        ->setLastModificationDate(now())
        ->setPriority(0.9)
        ->setChangeFrequency(\Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_DAILY));

    $sitemap->add(\Spatie\Sitemap\Tags\Url::create('/crystals')
        ->setLastModificationDate(now())
        ->setPriority(0.9)
        ->setChangeFrequency(\Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_DAILY));

    $sitemap->add(\Spatie\Sitemap\Tags\Url::create('/changelog')
        ->setLastModificationDate(now())
        ->setPriority(0.7)
        ->setChangeFrequency(\Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY));

    // Dynamic crystal pages
    $users = User::has('crystalMetrics')->get();
    foreach ($users as $user) {
        $sitemap->add(\Spatie\Sitemap\Tags\Url::create('/crystals/'.$user->id)
            ->setLastModificationDate($user->updated_at)
            ->setPriority(0.8)
            ->setChangeFrequency(\Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY));
    }

    return $sitemap->toResponse(request());
})->name('sitemap');
