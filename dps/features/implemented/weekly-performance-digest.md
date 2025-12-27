# Weekly Performance Digest - Creator Analytics & Retention

## Overview

Automated weekly email digest system that sends content creators a comprehensive performance summary every Monday morning. Includes views, downloads, rankings, crystal progress, and actionable insights to drive engagement and retention.

## Database Schema

### weekly_digest_stats Table
```php
Schema::create('weekly_digest_stats', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->date('week_start_date');
    $table->date('week_end_date');
    $table->integer('total_views')->default(0);
    $table->integer('total_downloads')->default(0);
    $table->integer('new_reviews')->default(0);
    $table->integer('new_ratings')->default(0);
    $table->json('top_5_content')->nullable(); // Pre-calculated top content
    $table->json('crystal_snapshot')->nullable(); // Rank, essence at time of digest
    $table->boolean('email_sent')->default(false);
    $table->timestamp('email_sent_at')->nullable();
    $table->timestamps();

    $table->unique(['user_id', 'week_start_date']);
    $table->index(['week_start_date', 'email_sent']);
    $table->index(['user_id', 'created_at']);
});
```

**Top 5 Content JSON Structure:**
```json
[
  {
    "id": 45,
    "title": "Ultimate Worldbuilding Guide",
    "slug": "ultimate-worldbuilding-guide",
    "views": 342,
    "downloads": 67,
    "new_reviews": 3,
    "new_ratings": 5,
    "average_rating": 4.8
  }
]
```

**Crystal Snapshot JSON Structure:**
```json
{
  "rank": "Journeyman",
  "level": 12,
  "facet_count": 12,
  "glow_intensity": 0.65,
  "purity_level": 0.78,
  "aura_percentage": 65,
  "essence_percentage": 78
}
```

## User Model Extensions

Add to `app/Models/User.php`:

**Relationship:**
```php
/**
 * Weekly digest statistics
 */
public function weeklyDigests(): HasMany
{
    return $this->hasMany(WeeklyDigestStat::class);
}
```

**Notification Preferences Extension:**

Update `notification_preferences` JSON to include:
```json
{
  "weekly_performance_digest": true,
  "resonance_received": true,
  ...existing preferences
}
```

**Methods:**
```php
/**
 * Check if user wants weekly digest emails
 */
public function wantsWeeklyDigest(): bool
{
    $preferences = $this->notification_preferences ?? [];
    return $preferences['weekly_performance_digest'] ?? true;
}

/**
 * Check if user is an active creator (has published content)
 */
public function isActiveCreator(): bool
{
    return $this->contents()
        ->whereIn('status', [Content::STATUS_PUBLIC, Content::STATUS_MEMBERS_ONLY])
        ->exists();
}
```

## Models

### WeeklyDigestStat Model (`app/Models/WeeklyDigestStat.php`)

**Fillable Fields:**
- user_id, week_start_date, week_end_date
- total_views, total_downloads, new_reviews, new_ratings
- top_5_content, crystal_snapshot
- email_sent, email_sent_at

**Casts:**
- week_start_date: date
- week_end_date: date
- top_5_content: array
- crystal_snapshot: array
- email_sent: boolean
- email_sent_at: datetime

**Relationships:**
- `user()`: BelongsTo User

**Scopes:**
- `forWeek(Carbon $startDate)`: Filter by week_start_date
- `unsent()`: email_sent = false
- `recent(int $weeks = 4)`: Last N weeks

**Methods:**
```php
/**
 * Check if digest has any activity
 */
public function hasActivity(): bool
{
    return $this->total_views > 0
        || $this->total_downloads > 0
        || $this->new_reviews > 0
        || $this->new_ratings > 0;
}

/**
 * Get formatted week range
 */
public function getWeekRangeFormatted(): string
{
    return $this->week_start_date->format('M d') . ' - ' .
           $this->week_end_date->format('M d, Y');
}

/**
 * Get top content item by metric
 */
public function getTopContentByMetric(string $metric = 'views'): ?array
{
    if (empty($this->top_5_content)) {
        return null;
    }

    $sorted = collect($this->top_5_content)->sortByDesc($metric);
    return $sorted->first();
}

/**
 * Calculate total engagement score
 */
public function getTotalEngagement(): int
{
    return $this->total_views +
           ($this->total_downloads * 3) +
           ($this->new_reviews * 5) +
           ($this->new_ratings * 2);
}
```

## Services

### WeeklyDigestService (`app/Services/WeeklyDigestService.php`)

**Purpose:** Calculate weekly statistics for all active creators

**Methods:**

```php
/**
 * Generate digest stats for a specific week
 */
public function generateDigestForWeek(Carbon $weekStart): int
{
    $weekEnd = $weekStart->copy()->endOfWeek();
    $generatedCount = 0;

    // Get all active creators who want weekly digests
    $creators = User::whereHas('contents', function($q) {
        $q->whereIn('status', [Content::STATUS_PUBLIC, Content::STATUS_MEMBERS_ONLY]);
    })
    ->get()
    ->filter(fn($user) => $user->wantsWeeklyDigest());

    foreach ($creators as $creator) {
        $stats = $this->calculateCreatorStats($creator, $weekStart, $weekEnd);

        // Only create digest if there's activity
        if ($stats['has_activity']) {
            WeeklyDigestStat::updateOrCreate(
                [
                    'user_id' => $creator->id,
                    'week_start_date' => $weekStart->toDateString(),
                ],
                [
                    'week_end_date' => $weekEnd->toDateString(),
                    'total_views' => $stats['total_views'],
                    'total_downloads' => $stats['total_downloads'],
                    'new_reviews' => $stats['new_reviews'],
                    'new_ratings' => $stats['new_ratings'],
                    'top_5_content' => $stats['top_5_content'],
                    'crystal_snapshot' => $stats['crystal_snapshot'],
                ]
            );

            $generatedCount++;
        }
    }

    return $generatedCount;
}

/**
 * Calculate statistics for a specific creator
 */
protected function calculateCreatorStats(User $creator, Carbon $weekStart, Carbon $weekEnd): array
{
    // Get all creator's published content
    $contentIds = $creator->contents()
        ->whereIn('status', [Content::STATUS_PUBLIC, Content::STATUS_MEMBERS_ONLY])
        ->pluck('id');

    if ($contentIds->isEmpty()) {
        return ['has_activity' => false];
    }

    // Calculate views (from content_views table or incremented counter)
    // Note: Assuming views are tracked via ContentViewedEvent
    $totalViews = CrystalActivityQueue::forUser($creator->id)
        ->ofType(CrystalActivityQueue::TYPE_CONTENT_VIEWED)
        ->whereBetween('created_at', [$weekStart, $weekEnd])
        ->count();

    // Calculate downloads
    $totalDownloads = ContentDownload::whereIn('content_id', $contentIds)
        ->whereBetween('downloaded_at', [$weekStart, $weekEnd])
        ->count();

    // Calculate new reviews
    $newReviews = ContentReview::whereIn('content_id', $contentIds)
        ->whereBetween('created_at', [$weekStart, $weekEnd])
        ->count();

    // Calculate new ratings
    $newRatings = ContentRating::whereIn('content_id', $contentIds)
        ->whereBetween('created_at', [$weekStart, $weekEnd])
        ->count();

    // Get top 5 content
    $top5Content = $this->getTop5Content($creator, $contentIds, $weekStart, $weekEnd);

    // Get crystal snapshot
    $crystalSnapshot = $this->getCrystalSnapshot($creator);

    return [
        'has_activity' => $totalViews > 0 || $totalDownloads > 0 || $newReviews > 0 || $newRatings > 0,
        'total_views' => $totalViews,
        'total_downloads' => $totalDownloads,
        'new_reviews' => $newReviews,
        'new_ratings' => $newRatings,
        'top_5_content' => $top5Content,
        'crystal_snapshot' => $crystalSnapshot,
    ];
}

/**
 * Get top 5 performing content for the week
 */
protected function getTop5Content(User $creator, $contentIds, Carbon $weekStart, Carbon $weekEnd): array
{
    $content = Content::whereIn('id', $contentIds)
        ->with(['ratings', 'reviews'])
        ->get();

    return $content->map(function($item) use ($weekStart, $weekEnd) {
        // Calculate week-specific metrics
        $weekViews = CrystalActivityQueue::where('activity_type', CrystalActivityQueue::TYPE_CONTENT_VIEWED)
            ->where('metadata->content_id', $item->id)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();

        $weekDownloads = ContentDownload::where('content_id', $item->id)
            ->whereBetween('downloaded_at', [$weekStart, $weekEnd])
            ->count();

        $weekReviews = ContentReview::where('content_id', $item->id)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();

        $weekRatings = ContentRating::where('content_id', $item->id)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();

        return [
            'id' => $item->id,
            'title' => $item->title,
            'slug' => $item->slug,
            'views' => $weekViews,
            'downloads' => $weekDownloads,
            'new_reviews' => $weekReviews,
            'new_ratings' => $weekRatings,
            'average_rating' => round($item->average_rating, 1),
            'engagement_score' => $weekViews + ($weekDownloads * 3) + ($weekReviews * 5) + ($weekRatings * 2),
        ];
    })
    ->sortByDesc('engagement_score')
    ->take(5)
    ->values()
    ->toArray();
}

/**
 * Get current crystal metrics snapshot
 */
protected function getCrystalSnapshot(User $creator): array
{
    $metric = $creator->crystalMetric;

    if (!$metric) {
        return [
            'rank' => 'Novice',
            'level' => 1,
            'facet_count' => 4,
            'glow_intensity' => 0.0,
            'purity_level' => 0.3,
            'aura_percentage' => 0,
            'essence_percentage' => 30,
        ];
    }

    return [
        'rank' => $creator->calculateRank($metric->facet_count),
        'level' => $metric->facet_count,
        'facet_count' => $metric->facet_count,
        'glow_intensity' => $metric->glow_intensity,
        'purity_level' => $metric->purity_level,
        'aura_percentage' => round($metric->glow_intensity * 100),
        'essence_percentage' => round($metric->purity_level * 100),
    ];
}

/**
 * Send pending digest emails
 */
public function sendPendingDigests(): int
{
    $pendingDigests = WeeklyDigestStat::unsent()
        ->with('user')
        ->get();

    $sentCount = 0;

    foreach ($pendingDigests as $digest) {
        try {
            Mail::to($digest->user->email)
                ->send(new WeeklyPerformanceDigestMail($digest));

            $digest->update([
                'email_sent' => true,
                'email_sent_at' => now(),
            ]);

            $sentCount++;
        } catch (\Exception $e) {
            Log::error('Failed to send weekly digest', [
                'user_id' => $digest->user_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    return $sentCount;
}
```

## Mailable Class

### WeeklyPerformanceDigestMail (`app/Mail/WeeklyPerformanceDigestMail.php`)

**Purpose:** Format and send weekly performance digest email

```php
<?php

namespace App\Mail;

use App\Models\WeeklyDigestStat;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyPerformanceDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WeeklyDigestStat $digest
    ) {}

    public function envelope(): Envelope
    {
        $weekRange = $this->digest->getWeekRangeFormatted();

        return new Envelope(
            subject: "Your Weekly Performance ({$weekRange}) 📊",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.weekly-performance-digest',
            with: [
                'digest' => $this->digest,
                'user' => $this->digest->user,
                'weekRange' => $this->digest->getWeekRangeFormatted(),
                'crystalSnapshot' => $this->digest->crystal_snapshot,
                'top5Content' => $this->digest->top_5_content,
                'forgeProfileUrl' => route('forge.profile', $this->digest->user->username),
            ],
        );
    }
}
```

## Email Template

### Blade Template (`resources/views/emails/weekly-performance-digest.blade.php`)

```blade
<x-mail::message>
# Weekly Performance Digest

Hello {{ $user->anonymized_name }},

Here's your creative performance summary for **{{ $weekRange }}**:

## 📊 Overview

<x-mail::panel>
**Total Views:** {{ number_format($digest->total_views) }}<br>
**Total Downloads:** {{ number_format($digest->total_downloads) }}<br>
**New Reviews:** {{ $digest->new_reviews }}<br>
**New Ratings:** {{ $digest->new_ratings }}
</x-mail::panel>

@if($crystalSnapshot)
## 🔮 Crystal Progress

Your current rank: **{{ $crystalSnapshot['rank'] }}** (Level {{ $crystalSnapshot['level'] }})<br>
Aura Resonance: **{{ $crystalSnapshot['aura_percentage'] }}%**<br>
Essence Clarity: **{{ $crystalSnapshot['essence_percentage'] }}%**
@endif

@if(!empty($top5Content) && count($top5Content) > 0)
## 🏆 Top 5 Performing Content

@foreach($top5Content as $index => $content)
### {{ $index + 1 }}. {{ $content['title'] }}

- **Views:** {{ number_format($content['views']) }}
- **Downloads:** {{ number_format($content['downloads']) }}
@if($content['new_reviews'] > 0)
- **New Reviews:** {{ $content['new_reviews'] }} ⭐
@endif
@if($content['new_ratings'] > 0)
- **New Ratings:** {{ $content['new_ratings'] }} (Avg: {{ $content['average_rating'] }}/5)
@endif

<x-mail::button :url="route('content.edit', $content['slug'])">
Edit Content
</x-mail::button>

---
@endforeach
@endif

## 🎯 Quick Actions

<x-mail::button :url="$forgeProfileUrl">
View Forge Profile
</x-mail::button>

<x-mail::button :url="route('content.create')">
Create New Content
</x-mail::button>

---

Keep creating amazing content! Your work is making an impact.

Thanks,<br>
{{ config('app.name') }}

<x-mail::subcopy>
Don't want these weekly updates? You can disable them in your [profile settings]({{ route('profile.edit') }}).
</x-mail::subcopy>
</x-mail::message>
```

### Alternative HTML Template with Visual Crystal

Create `resources/views/emails/weekly-performance-digest-visual.blade.php` for enhanced version:

```blade
@component('mail::message')
<div style="text-align: center; margin-bottom: 30px;">
    <h1 style="color: #d97706; font-size: 28px; margin-bottom: 10px;">
        📊 Weekly Performance Digest
    </h1>
    <p style="color: #6b7280; font-size: 16px;">{{ $weekRange }}</p>
</div>

{{-- Stats Cards Grid --}}
<table style="width: 100%; margin: 20px 0;">
    <tr>
        <td style="width: 50%; padding: 10px;">
            <div style="background: #fef3c7; border-radius: 8px; padding: 20px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold; color: #d97706;">
                    {{ number_format($digest->total_views) }}
                </div>
                <div style="color: #92400e; font-size: 14px; margin-top: 5px;">
                    👁️ Total Views
                </div>
            </div>
        </td>
        <td style="width: 50%; padding: 10px;">
            <div style="background: #ddd6fe; border-radius: 8px; padding: 20px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold; color: #7c3aed;">
                    {{ number_format($digest->total_downloads) }}
                </div>
                <div style="color: #5b21b6; font-size: 14px; margin-top: 5px;">
                    ⬇️ Total Downloads
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td style="width: 50%; padding: 10px;">
            <div style="background: #fce7f3; border-radius: 8px; padding: 20px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold; color: #ec4899;">
                    {{ $digest->new_reviews }}
                </div>
                <div style="color: #9f1239; font-size: 14px; margin-top: 5px;">
                    💬 New Reviews
                </div>
            </div>
        </td>
        <td style="width: 50%; padding: 10px;">
            <div style="background: #ccfbf1; border-radius: 8px; padding: 20px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold; color: #14b8a6;">
                    {{ $digest->new_ratings }}
                </div>
                <div style="color: #115e59; font-size: 14px; margin-top: 5px;">
                    ⭐ New Ratings
                </div>
            </div>
        </td>
    </tr>
</table>

{{-- Crystal Progress Section --}}
@if($crystalSnapshot)
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 25px; color: white; margin: 30px 0;">
    <h2 style="color: white; font-size: 20px; margin-bottom: 15px; text-align: center;">
        🔮 Your Crystal Progress
    </h2>
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">
            {{ $crystalSnapshot['rank'] }}
        </div>
        <div style="font-size: 14px; opacity: 0.9;">
            Level {{ $crystalSnapshot['level'] }} • {{ $crystalSnapshot['facet_count'] }} Facets
        </div>
    </div>

    <table style="width: 100%;">
        <tr>
            <td style="width: 50%; padding: 10px;">
                <div style="font-size: 14px; opacity: 0.8; margin-bottom: 5px;">Aura Resonance</div>
                <div style="background: rgba(255,255,255,0.2); border-radius: 8px; padding: 8px;">
                    <div style="background: #fbbf24; height: 8px; border-radius: 4px; width: {{ $crystalSnapshot['aura_percentage'] }}%;"></div>
                </div>
                <div style="font-size: 16px; font-weight: bold; margin-top: 5px;">
                    {{ $crystalSnapshot['aura_percentage'] }}%
                </div>
            </td>
            <td style="width: 50%; padding: 10px;">
                <div style="font-size: 14px; opacity: 0.8; margin-bottom: 5px;">Essence Clarity</div>
                <div style="background: rgba(255,255,255,0.2); border-radius: 8px; padding: 8px;">
                    <div style="background: #60a5fa; height: 8px; border-radius: 4px; width: {{ $crystalSnapshot['essence_percentage'] }}%;"></div>
                </div>
                <div style="font-size: 16px; font-weight: bold; margin-top: 5px;">
                    {{ $crystalSnapshot['essence_percentage'] }}%
                </div>
            </td>
        </tr>
    </table>
</div>
@endif

{{-- Top 5 Content --}}
@if(!empty($top5Content) && count($top5Content) > 0)
<h2 style="color: #1f2937; font-size: 20px; margin: 30px 0 15px 0;">
    🏆 Your Top 5 Performing Content
</h2>

@foreach($top5Content as $index => $content)
<div style="border: 2px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 15px;">
    <div style="display: flex; align-items: center; margin-bottom: 10px;">
        <span style="background: #d97706; color: white; width: 30px; height: 30px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 10px;">
            {{ $index + 1 }}
        </span>
        <h3 style="color: #1f2937; font-size: 16px; margin: 0;">
            {{ $content['title'] }}
        </h3>
    </div>

    <table style="width: 100%; margin: 10px 0;">
        <tr>
            <td style="padding: 5px 10px; font-size: 14px; color: #6b7280;">
                👁️ <strong>{{ number_format($content['views']) }}</strong> views
            </td>
            <td style="padding: 5px 10px; font-size: 14px; color: #6b7280;">
                ⬇️ <strong>{{ number_format($content['downloads']) }}</strong> downloads
            </td>
        </tr>
        @if($content['new_reviews'] > 0 || $content['new_ratings'] > 0)
        <tr>
            @if($content['new_reviews'] > 0)
            <td style="padding: 5px 10px; font-size: 14px; color: #6b7280;">
                💬 <strong>{{ $content['new_reviews'] }}</strong> new reviews
            </td>
            @endif
            @if($content['new_ratings'] > 0)
            <td style="padding: 5px 10px; font-size: 14px; color: #6b7280;">
                ⭐ <strong>{{ $content['new_ratings'] }}</strong> ratings ({{ $content['average_rating'] }}/5)
            </td>
            @endif
        </tr>
        @endif
    </table>

    @component('mail::button', ['url' => route('content.edit', $content['slug']), 'color' => 'primary'])
    Edit Content
    @endcomponent
</div>
@endforeach
@endif

{{-- Call to Action --}}
<div style="text-align: center; margin: 40px 0;">
    @component('mail::button', ['url' => $forgeProfileUrl])
    View Your Forge Profile
    @endcomponent

    @component('mail::button', ['url' => route('content.create'), 'color' => 'success'])
    Create New Content
    @endcomponent
</div>

<hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">

<p style="color: #6b7280; font-size: 14px; text-align: center;">
    Keep creating amazing content! Your work is making an impact. 🚀
</p>

@component('mail::subcopy')
Don't want these weekly updates? You can [disable them]({{ route('profile.edit') }}) in your profile settings.
@endcomponent

@endcomponent
```

## Artisan Commands

### GenerateWeeklyDigestsCommand (`app/Console/Commands/GenerateWeeklyDigestsCommand.php`)

**Signature:** `digest:generate {--week-start= : Week start date (Y-m-d)}`

**Schedule:** Every Sunday at 11:00 PM

**Purpose:** Calculate statistics for the past week

```php
<?php

namespace App\Console\Commands;

use App\Services\WeeklyDigestService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateWeeklyDigestsCommand extends Command
{
    protected $signature = 'digest:generate {--week-start= : Week start date (Y-m-d)}';
    protected $description = 'Generate weekly performance digests for all active creators';

    public function handle(WeeklyDigestService $digestService)
    {
        $weekStart = $this->option('week-start')
            ? Carbon::parse($this->option('week-start'))->startOfWeek()
            : Carbon::now()->subWeek()->startOfWeek();

        $this->info("Generating digests for week: {$weekStart->toDateString()}");

        $generated = $digestService->generateDigestForWeek($weekStart);

        $this->info("Generated {$generated} weekly digests");

        return self::SUCCESS;
    }
}
```

### SendWeeklyDigestsCommand (`app/Console/Commands/SendWeeklyDigestsCommand.php`)

**Signature:** `digest:send`

**Schedule:** Every Monday at 8:00 AM

**Purpose:** Send all pending digest emails

```php
<?php

namespace App\Console\Commands;

use App\Services\WeeklyDigestService;
use Illuminate\Console\Command;

class SendWeeklyDigestsCommand extends Command
{
    protected $signature = 'digest:send';
    protected $description = 'Send pending weekly performance digest emails';

    public function handle(WeeklyDigestService $digestService)
    {
        $this->info('Sending pending weekly digests...');

        $sent = $digestService->sendPendingDigests();

        $this->info("Sent {$sent} digest emails");

        return self::SUCCESS;
    }
}
```

### Schedule Configuration (`app/Console/Kernel.php`)

```php
protected function schedule(Schedule $schedule)
{
    // ... existing schedules

    // Generate weekly digests every Sunday at 11 PM
    $schedule->command('digest:generate')
        ->weeklyOn(0, '23:00')
        ->timezone('Europe/Budapest');

    // Send weekly digests every Monday at 8 AM
    $schedule->command('digest:send')
        ->weeklyOn(1, '08:00')
        ->timezone('Europe/Budapest');
}
```

## Filament Admin Resources

### WeeklyDigestStatResource (`app/Filament/Admin/Resources/WeeklyDigestStatResource.php`)

**Navigation:**
- Icon: heroicon-o-chart-bar
- Group: System Settings
- Sort: 15
- Label: Weekly Digests

**Table Columns:**
- User (anonymized name, searchable)
- Week Range (formatted: "Dec 16 - Dec 22, 2025")
- Total Views (sortable, badge)
- Total Downloads (sortable, badge)
- New Reviews (sortable)
- New Ratings (sortable)
- Email Sent (boolean icon)
- Email Sent At (datetime)

**Filters:**
- Week (date filter on week_start_date)
- Email Sent (ternary: sent/pending/all)
- Has Activity (toggle: filters digests with total engagement > 0)

**Actions:**
- View Details (slide-over modal with full stats)
- Resend Email (admin override)
- Delete

**Bulk Actions:**
- Send Selected Digests
- Delete Selected

**Infolist (View Details):**
- User Info (name, email, Forge profile link)
- Week Range (formatted)
- Statistics (views, downloads, reviews, ratings, total engagement)
- Top 5 Content (table with titles and metrics)
- Crystal Snapshot (rank, level, aura, essence)
- Email Status (sent/pending with timestamp)

**Custom Actions:**
- **Regenerate**: Recalculate stats for the week
- **Preview Email**: Show email content in modal
- **Send Now**: Force send immediately

**Widgets:**
- Total Digests Sent This Month
- Average Engagement Score
- Active Recipients Count

### User Profile Settings Integration

Add to Filament's user profile edit page or create custom settings page:

**File:** `app/Filament/Admin/Pages/ProfileSettings.php`

Add notification preferences section:

```php
Forms\Components\Section::make('Email Notifications')
    ->schema([
        Forms\Components\Toggle::make('notification_preferences.weekly_performance_digest')
            ->label('Weekly Performance Digest')
            ->helperText('Receive a summary of your content performance every Monday')
            ->default(true),

        Forms\Components\Toggle::make('notification_preferences.resonance_received')
            ->label('Resonance Notifications')
            ->helperText('Get notified when someone resonates with your crystal')
            ->default(true),

        // ... other notification preferences
    ]),
```

## Frontend Components

### Profile Settings Page (`resources/views/profile/edit.blade.php`)

Add notification preferences section:

```blade
<div class="settings-section">
    <h2>Email Notifications</h2>

    <div class="notification-preferences">
        <label class="preference-item">
            <input
                type="checkbox"
                name="notification_preferences[weekly_performance_digest]"
                {{ ($user->notification_preferences['weekly_performance_digest'] ?? true) ? 'checked' : '' }}
            >
            <div>
                <strong>Weekly Performance Digest</strong>
                <p>Receive a summary of your content performance every Monday morning</p>
            </div>
        </label>

        <label class="preference-item">
            <input
                type="checkbox"
                name="notification_preferences[resonance_received]"
                {{ ($user->notification_preferences['resonance_received'] ?? true) ? 'checked' : '' }}
            >
            <div>
                <strong>Resonance Notifications</strong>
                <p>Get notified when someone resonates with your crystal</p>
            </div>
        </label>

        <!-- More preferences -->
    </div>
</div>
```

### Dashboard Widget (`app/Filament/Admin/Widgets/WeeklyDigestPreviewWidget.php`)

**Purpose:** Show creator's own weekly stats on dashboard

```php
<?php

namespace App\Filament\Admin\Widgets;

use App\Models\WeeklyDigestStat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WeeklyDigestPreviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $lastDigest = WeeklyDigestStat::where('user_id', $user->id)
            ->latest('week_start_date')
            ->first();

        if (!$lastDigest) {
            return [];
        }

        return [
            Stat::make('Last Week Views', number_format($lastDigest->total_views))
                ->description($lastDigest->getWeekRangeFormatted())
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),

            Stat::make('Last Week Downloads', number_format($lastDigest->total_downloads))
                ->description($lastDigest->getWeekRangeFormatted())
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('info'),

            Stat::make('New Engagement', $lastDigest->new_reviews + $lastDigest->new_ratings)
                ->description('Reviews & Ratings')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
        ];
    }
}
```

## Testing Requirements

### Unit Tests

**WeeklyDigestStatTest:**
- `test_digest_has_correct_relationships()`
- `test_digest_detects_activity()`
- `test_digest_formats_week_range()`
- `test_digest_calculates_engagement_score()`
- `test_digest_gets_top_content_by_metric()`

**WeeklyDigestServiceTest:**
- `test_service_generates_digests_for_active_creators()`
- `test_service_skips_creators_without_opt_in()`
- `test_service_calculates_stats_correctly()`
- `test_service_creates_crystal_snapshot()`
- `test_service_identifies_top_5_content()`
- `test_service_only_includes_digests_with_activity()`

**UserModelTest:**
- `test_user_wants_weekly_digest_checks_preferences()`
- `test_user_is_active_creator_with_published_content()`
- `test_user_is_not_active_creator_without_content()`

### Feature Tests

**WeeklyDigestGenerationTest:**
- `test_command_generates_digests_for_previous_week()`
- `test_command_generates_for_custom_week()`
- `test_command_only_includes_opted_in_users()`
- `test_command_calculates_views_correctly()`
- `test_command_calculates_downloads_correctly()`
- `test_command_tracks_reviews_and_ratings()`

**WeeklyDigestEmailTest:**
- `test_digest_email_sends_to_user()`
- `test_digest_email_marks_as_sent()`
- `test_digest_email_contains_correct_data()`
- `test_digest_email_includes_crystal_snapshot()`
- `test_digest_email_includes_top_5_content()`
- `test_digest_email_includes_action_links()`

**NotificationPreferencesTest:**
- `test_user_can_opt_out_of_digests()`
- `test_user_can_opt_in_to_digests()`
- `test_preferences_persist_correctly()`
- `test_digests_not_sent_to_opted_out_users()`

**AdminResourceTest:**
- `test_admin_can_view_digest_list()`
- `test_admin_can_resend_digest()`
- `test_admin_can_regenerate_stats()`
- `test_admin_can_preview_email()`

## Configuration

Add to `config/digest.php`:

```php
return [
    // Schedule settings
    'generation' => [
        'day' => 0, // Sunday (0-6, 0 = Sunday)
        'time' => '23:00',
        'timezone' => 'Europe/Budapest',
    ],

    'sending' => [
        'day' => 1, // Monday
        'time' => '08:00',
        'timezone' => 'Europe/Budapest',
    ],

    // Week calculation
    'week_start_day' => Carbon::MONDAY, // Week starts on Monday

    // Engagement scoring
    'engagement_weights' => [
        'view' => 1,
        'download' => 3,
        'rating' => 2,
        'review' => 5,
    ],

    // Top content settings
    'top_content_limit' => 5,
    'min_engagement_for_top' => 5, // Minimum engagement to be included

    // Email settings
    'send_only_with_activity' => true, // Only send if there's activity
    'min_activity_threshold' => 1, // Minimum total views/downloads to send

    // Data retention
    'keep_digests_days' => 365, // Keep digests for 1 year
    'cleanup_old_digests' => true,

    // Default preferences
    'default_opt_in' => true, // New users opt-in by default
];
```

## Performance Optimization

### Database Indexes
- `weekly_digest_stats`: unique(user_id, week_start_date), (week_start_date, email_sent)
- Add indexes to `crystal_activity_queue` for date range queries
- Index `content_ratings.created_at` and `content_reviews.created_at`

### Caching Strategy
- Cache top content calculations (5 min TTL during digest generation)
- Cache crystal snapshots (1 min TTL)
- Use chunking for large user sets (process 100 users at a time)

### Queue Jobs
- `GenerateWeeklyDigestJob`: Background job for each user's stats
- `SendDigestEmailJob`: Queue individual emails
- Use `batch()` for processing multiple users

### Optimization Tips
```php
// In WeeklyDigestService, use chunking:
User::whereHas('contents')->chunk(100, function($users) {
    foreach ($users as $user) {
        GenerateWeeklyDigestJob::dispatch($user, $weekStart);
    }
});
```

## Security & Privacy

### Data Protection
- Anonymize user names in all emails
- Don't expose sensitive metrics publicly
- Respect user email preferences
- Use unsubscribe links in all emails

### Validation
- Validate week dates in commands
- Verify user permissions for admin actions
- Rate limit email sending (prevent spam)
- CSRF protection on settings forms

### Privacy Compliance
- Include unsubscribe link in footer
- Honor opt-out immediately
- Delete old digests after retention period
- Don't share digest data with third parties

## Implementation Order

1. Create migration for `weekly_digest_stats` table
2. Create `WeeklyDigestStat` model
3. Extend User model with notification preferences
4. Create `WeeklyDigestService` with calculation logic
5. Create `WeeklyPerformanceDigestMail` mailable
6. Create Blade email templates (markdown and visual)
7. Create artisan commands (generate, send)
8. Schedule commands in Kernel
9. Create Filament admin resource
10. Add notification preferences to profile settings
11. Create dashboard widget for creators
12. Write unit and feature tests
13. Add configuration file
14. Test complete generation → sending flow
15. Deploy and monitor first week

## Migration Checklist

- [ ] Run migration: `php artisan migrate`
- [ ] Add scheduled commands to Kernel.php
- [ ] Test digest generation: `php artisan digest:generate`
- [ ] Test email sending: `php artisan digest:send`
- [ ] Verify email templates render correctly
- [ ] Test opt-out functionality in profile settings
- [ ] Test admin resource (view, resend, regenerate)
- [ ] Verify crystal snapshot data is correct
- [ ] Test top 5 content calculation
- [ ] Verify only active creators receive emails
- [ ] Test with different activity levels (0, low, high)
- [ ] Check email deliverability
- [ ] Monitor first week's automated run
- [ ] Review dashboard widget display
- [ ] Test timezone handling

## Future Enhancements

- **Monthly Digest**: Extended version with month-over-month comparisons
- **Custom Digest Frequency**: Let users choose weekly/biweekly/monthly
- **Content Recommendations**: Suggest improvements based on performance
- **Comparative Analytics**: Show how user ranks against similar creators
- **Engagement Predictions**: ML-based predictions for content performance
- **Interactive Email**: AMP email support for in-email interactions
- **Mobile App Push**: Extend to mobile notifications
- **Export Reports**: PDF export of digest data
