<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContentDownloadController;
use App\Http\Controllers\ContentLibraryController;
use App\Http\Controllers\CrystalGalleryController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ForgeController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\NotificationController;
use App\Models\Post;
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

    // Get latest 4 blog posts
    $latestPosts = Post::published()
        ->with('author')
        ->orderBy('published_at', 'desc')
        ->limit(4)
        ->get();

    // Get total users count
    $stats = [
        'total_users' => User::count(),
    ];

    return view('welcome', [
        'topCrystals' => $topCrystals,
        'featuredContent' => $featuredContent,
        'latestPosts' => $latestPosts,
        'stats' => $stats,
    ]);
});

// Crystal Gallery Routes
Route::get('/crystals', [CrystalGalleryController::class, 'index'])->name('crystals.gallery');
Route::get('/crystals/{user}', [CrystalGalleryController::class, 'show'])->name('crystals.show');

// The Forge - User Profile Routes
Route::get('/forge/{user:username}', [ForgeController::class, 'show'])->name('forge.profile');

// Content Library Route
Route::get('/library', [ContentLibraryController::class, 'index'])->name('library.index');

// Blog Routes
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post}', [BlogController::class, 'show'])->name('blog.show');

// Changelog Route
Route::get('/changelog', [\App\Http\Controllers\ChangeLogController::class, 'index'])->name('changelog.index');

// Content Download Route
Route::get('/content/{content}/download', [ContentDownloadController::class, 'download'])
    ->middleware('auth')
    ->name('content.download');

// Invitation Routes
Route::get('/invitations/{token}/accept', [InvitationController::class, 'accept'])
    ->name('invitations.accept');

// Follow System Routes (authenticated only)
Route::middleware('auth')->group(function () {
    Route::post('/users/{user:username}/follow', [FollowController::class, 'follow'])->name('users.follow');
    Route::delete('/users/{user:username}/follow', [FollowController::class, 'unfollow'])->name('users.unfollow');
    Route::get('/users/{user:username}/follow-status', [FollowController::class, 'status'])->name('users.follow-status');
});

// Notification Routes (authenticated only)
Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::patch('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/read/clear', [NotificationController::class, 'clearRead'])->name('clear-read');
});

// Custom Email Verification Route (unauthenticated)
Route::get('/email-verification/{id}/{hash}', \App\Http\Controllers\Auth\EmailVerificationController::class)
    ->middleware(['throttle:6,1'])
    ->name('custom.email-verification.verify');

// Sitemap Route
Route::get('/sitemap.xml', function () {
    return cache()->remember('sitemap', now()->addHours(12), function () {
        return \Spatie\Sitemap\Sitemap::create()
            // Static pages
            ->add(\Spatie\Sitemap\Tags\Url::create('/')
                ->setLastModificationDate(now())
                ->setPriority(1.0)
                ->setChangeFrequency(\Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_DAILY))
            ->add(\Spatie\Sitemap\Tags\Url::create('/library')
                ->setLastModificationDate(now())
                ->setPriority(0.9)
                ->setChangeFrequency(\Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_DAILY))
            ->add(\Spatie\Sitemap\Tags\Url::create('/crystals')
                ->setLastModificationDate(now())
                ->setPriority(0.9)
                ->setChangeFrequency(\Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_DAILY))
            ->add(\Spatie\Sitemap\Tags\Url::create('/changelog')
                ->setLastModificationDate(now())
                ->setPriority(0.7)
                ->setChangeFrequency(\Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY))
            // Dynamic crystal pages using Sitemapable interface
            ->add(User::has('crystalMetric')->get())
            // Forge profile pages
            ->add(User::whereNotNull('username')
                ->has('crystalMetric')
                ->get()
                ->map(fn($user) =>
                    \Spatie\Sitemap\Tags\Url::create("/forge/{$user->username}")
                        ->setLastModificationDate($user->crystalMetric->last_calculated_at ?? $user->updated_at)
                        ->setPriority(0.8)
                        ->setChangeFrequency(\Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY)
                ))
            ->toResponse(request());
    });
})->name('sitemap');
