# Crystal Gamification System Restoration Plan

## Overview
Restore the Crystal Gamification System from git history (commit `ff4b46b`), placing code in the main app directory.

## Source Commit
- **Commit**: `ff4b46b` (Crystal Basics - Dec 6, 2025)
- **Removed in**: `c9d0fa6` (Dec 7, 2025)

---

## Phase 1: Extract Files from Git History

### Create Directories
```bash
mkdir -p app/Events app/Jobs app/Listeners app/Http/Controllers/Api resources/views/crystals resources/js/components
```

### Extract Backend Files
```bash
# Models
git show ff4b46b:packages/content-engine/src/Models/UserCrystalMetric.php > app/Models/UserCrystalMetric.php
git show ff4b46b:packages/content-engine/src/Models/ContentRating.php > app/Models/ContentRating.php
git show ff4b46b:packages/content-engine/src/Models/CrystalActivityQueue.php > app/Models/CrystalActivityQueue.php

# Controllers
git show ff4b46b:app/Http/Controllers/CrystalGalleryController.php > app/Http/Controllers/CrystalGalleryController.php
git show ff4b46b:packages/content-engine/src/Http/Controllers/Api/CrystalApiController.php > app/Http/Controllers/Api/CrystalApiController.php

# Services
git show ff4b46b:packages/content-engine/src/Services/CrystalCalculatorService.php > app/Services/CrystalCalculatorService.php

# Events, Jobs, Listeners, Commands
git show ff4b46b:packages/content-engine/src/Events/AchievementUnlockedEvent.php > app/Events/AchievementUnlockedEvent.php
git show ff4b46b:packages/content-engine/src/Jobs/RecalculateCrystalMetricsJob.php > app/Jobs/RecalculateCrystalMetricsJob.php
git show ff4b46b:packages/content-engine/src/Listeners/QueueCrystalUpdateListener.php > app/Listeners/QueueCrystalUpdateListener.php
git show ff4b46b:packages/content-engine/src/Console/Commands/ProcessCrystalUpdatesCommand.php > app/Console/Commands/ProcessCrystalUpdatesCommand.php
```

### Extract Migrations (with new dates)
```bash
git show ff4b46b:packages/content-engine/database/migrations/2024_12_06_000005_create_user_crystal_metrics_table.php > database/migrations/2024_12_14_000001_create_user_crystal_metrics_table.php
git show ff4b46b:packages/content-engine/database/migrations/2024_12_06_000006_create_content_ratings_table.php > database/migrations/2024_12_14_000002_create_content_ratings_table.php
git show ff4b46b:packages/content-engine/database/migrations/2024_12_06_000007_create_crystal_activity_queue_table.php > database/migrations/2024_12_14_000003_create_crystal_activity_queue_table.php
```

### Extract Frontend Files
```bash
git show ff4b46b:resources/views/crystals/gallery.blade.php > resources/views/crystals/gallery.blade.php
git show ff4b46b:resources/views/crystals/show.blade.php > resources/views/crystals/show.blade.php
git show ff4b46b:resources/css/crystal-viewer.css > resources/css/crystal-viewer.css
git show ff4b46b:resources/js/components/CrystalViewer.js > resources/js/components/CrystalViewer.js
```

---

## Phase 2: Namespace Modifications

### Models (change `Webtechsolutions\ContentEngine\Models` to `App\Models`)
| File | Changes |
|------|---------|
| `app/Models/UserCrystalMetric.php` | Namespace to `App\Models` |
| `app/Models/ContentRating.php` | Namespace to `App\Models`, keep `Webtechsolutions\ContentEngine\Models\Content` import |
| `app/Models/CrystalActivityQueue.php` | Namespace to `App\Models` |

### Controllers
| File | Changes |
|------|---------|
| `app/Http/Controllers/CrystalGalleryController.php` | Update model imports to `App\Models\*` |
| `app/Http/Controllers/Api/CrystalApiController.php` | Namespace to `App\Http\Controllers\Api`, model imports to `App\Models\*`, keep `Webtechsolutions\ContentEngine\Models\Content` and `Webtechsolutions\ContentEngine\Events\ContentRatedEvent` |

### Services
| File | Changes |
|------|---------|
| `app/Services/CrystalCalculatorService.php` | Namespace to `App\Services`, model imports to `App\Models\*`, keep Content import from package |

### Events, Jobs, Listeners, Commands
| File | Changes |
|------|---------|
| `app/Events/AchievementUnlockedEvent.php` | Namespace to `App\Events` |
| `app/Jobs/RecalculateCrystalMetricsJob.php` | Namespace to `App\Jobs`, imports to `App\Models\*` and `App\Services\*` |
| `app/Listeners/QueueCrystalUpdateListener.php` | Namespace to `App\Listeners`, imports to `App\Models\*` and `App\Events\*`, keep package events |
| `app/Console/Commands/ProcessCrystalUpdatesCommand.php` | Namespace to `App\Console\Commands`, fix string interpolation bug in `handle()` |

---

## Phase 3: Route Registration

### Create `routes/api.php`
```php
<?php

use App\Http\Controllers\Api\CrystalApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/crystals/gallery', [CrystalApiController::class, 'gallery']);
    Route::get('/crystals/leaderboard', [CrystalApiController::class, 'leaderboard']);
    Route::get('/crystals/{userId}', [CrystalApiController::class, 'show']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/content/{contentId}/rate', [CrystalApiController::class, 'rateContent']);
        Route::post('/content/{contentId}/rate/{ratingId}/helpful', [CrystalApiController::class, 'markRatingHelpful']);
    });
});
```

### Modify `routes/web.php`
Add crystal routes:
```php
use App\Http\Controllers\CrystalGalleryController;

Route::get('/crystals', [CrystalGalleryController::class, 'index'])->name('crystals.gallery');
Route::get('/crystals/{user}', [CrystalGalleryController::class, 'show'])->name('crystals.show');
```

### Modify `bootstrap/app.php`
Add API routes to `withRouting()`:
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',  // ADD THIS
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```
Note: Scheduler entry for `crystal:process-updates` already exists (lines 42-45).

---

## Phase 4: Event & Service Registration

### Modify `app/Providers/AppServiceProvider.php`

**In `register()`:**
```php
use App\Services\CrystalCalculatorService;

$this->app->singleton(CrystalCalculatorService::class);
```

**In `boot()`:**
```php
use App\Listeners\QueueCrystalUpdateListener;
use Illuminate\Support\Facades\Event;
use Webtechsolutions\ContentEngine\Events\ContentPublishedEvent;
use Webtechsolutions\ContentEngine\Events\ContentViewedEvent;
use Webtechsolutions\ContentEngine\Events\ContentDownloadedEvent;
use Webtechsolutions\ContentEngine\Events\ContentRatedEvent;

Event::listen(ContentPublishedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentPublished']);
Event::listen(ContentViewedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentViewed']);
Event::listen(ContentDownloadedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentDownloaded']);
Event::listen(ContentRatedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentRated']);
```

---

## Phase 5: Model Relationship

### Modify `app/Models/User.php`
Add import and relationship:
```php
use App\Models\UserCrystalMetric;

public function crystalMetric(): HasOne
{
    return $this->hasOne(UserCrystalMetric::class);
}
```

---

## Phase 6: Frontend Integration

### Install Three.js
```bash
docker exec vilagmuhely-php-fpm-1 npm install three@^0.170.0
```

### Modify `resources/css/app.css`
Add import:
```css
@import './crystal-viewer.css';
```

### Modify `resources/js/app.js`
Add import:
```javascript
import './components/CrystalViewer.js';
```

---

## Phase 7: Database & Build

### Run Migrations
```bash
docker exec vilagmuhely-php-fpm-1 php artisan migrate
```

### Build Assets
```bash
docker exec vilagmuhely-php-fpm-1 npm run build
```

### Clear Caches
```bash
docker exec vilagmuhely-php-fpm-1 php artisan optimize:clear
```

---

## Files Summary

### New Files (17)
| Path | Description |
|------|-------------|
| `app/Models/UserCrystalMetric.php` | Crystal metrics model |
| `app/Models/ContentRating.php` | Content rating model |
| `app/Models/CrystalActivityQueue.php` | Activity queue model |
| `app/Http/Controllers/CrystalGalleryController.php` | Web gallery controller |
| `app/Http/Controllers/Api/CrystalApiController.php` | API controller |
| `app/Services/CrystalCalculatorService.php` | Calculation service |
| `app/Events/AchievementUnlockedEvent.php` | Achievement event |
| `app/Jobs/RecalculateCrystalMetricsJob.php` | Recalculation job |
| `app/Listeners/QueueCrystalUpdateListener.php` | Event listener |
| `app/Console/Commands/ProcessCrystalUpdatesCommand.php` | Artisan command |
| `database/migrations/2024_12_14_000001_create_user_crystal_metrics_table.php` | Migration |
| `database/migrations/2024_12_14_000002_create_content_ratings_table.php` | Migration |
| `database/migrations/2024_12_14_000003_create_crystal_activity_queue_table.php` | Migration |
| `resources/views/crystals/gallery.blade.php` | Gallery view |
| `resources/views/crystals/show.blade.php` | Detail view |
| `resources/css/crystal-viewer.css` | Crystal styles |
| `resources/js/components/CrystalViewer.js` | Three.js component |
| `routes/api.php` | API routes |

### Modified Files (6)
| Path | Description |
|------|-------------|
| `routes/web.php` | Add crystal routes |
| `bootstrap/app.php` | Add API routes registration |
| `app/Providers/AppServiceProvider.php` | Register events & service |
| `app/Models/User.php` | Add crystalMetric relationship |
| `resources/css/app.css` | Import crystal CSS |
| `resources/js/app.js` | Import CrystalViewer |

---

## Verification

```bash
# Test command
docker exec vilagmuhely-php-fpm-1 php artisan crystal:process-updates

# Test API
curl http://localhost:8000/api/v1/crystals/gallery
curl http://localhost:8000/api/v1/crystals/1

# Test web
# Visit http://localhost:8000/crystals
```
