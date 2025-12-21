# Feature: Crystal Growth Milestone System

## Overview
Extend the crystal growth system to reward users for achieving specific milestones in user engagement, content reach, and community building activities.

## Technical Implementation

### 1. New Crystal Activity Types

Add to `app/Models/CrystalActivityQueue.php`:
```php
public const TYPE_INVITATION_SENT = 'invitation_sent';
public const TYPE_INVITATION_ACCEPTED = 'invitation_accepted';
public const TYPE_CONTENT_MILESTONE_VIEWS = 'content_milestone_views';
public const TYPE_CONTENT_MILESTONE_DOWNLOADS = 'content_milestone_downloads';
```

### 2. Milestone Tracking

#### 2.1 Invitation Events

**When user sends invitation:**
- Activity Type: `invitation_sent`
- Triggered: When `Invitation` model is created
- Affected User: The inviter (`invited_by_user_id`)
- Implementation: Create observer or add to invitation creation logic

**When invited user accepts:**
- Activity Type: `invitation_accepted`
- Triggered: When invitation status changes to 'accepted' in `HandleInvitationAcceptance` listener
- Affected Users:
  - Inviter (`invited_by_user_id`) - gets crystal growth
  - Invited user (`accepted_by_user_id`) - gets crystal growth

#### 2.2 Content Milestone Views

**Milestone thresholds:** 10, 25, 50, 100, 250, 500, 1000, 2500, 5000, 10000
- Activity Type: `content_milestone_views`
- Triggered: When `views_count` crosses a threshold
- Implementation: Add to `ContentViewedEvent` listener
- Metadata: `{'content_id': X, 'milestone': 100, 'views_count': 102}`
- Track in content metadata to prevent duplicate triggers

#### 2.3 Content Milestone Downloads

**Milestone thresholds:** 1, 3, 5, 10, 25, 50, 100, 250, 500, 1000
- Activity Type: `content_milestone_downloads`
- Triggered: When `downloads_count` crosses a threshold
- Implementation: Add to `ContentDownloadedEvent` listener
- Metadata: `{'content_id': X, 'milestone': 10, 'downloads_count': 11}`
- Track in content metadata to prevent duplicate triggers

#### 2.4 Content Review Milestone

**Trigger:** Content receives its first review with text (not just rating)
- Activity Type: Already exists (`TYPE_CONTENT_REVIEWED`)
- Current implementation: Already queues crystal update
- Enhancement: Add milestone detection for first review

#### 2.5 Content Rating Threshold

**Trigger:** Content accumulates 3+ total ratings
- Check in `ContentRatedEvent` listener
- Only trigger once per content when count reaches exactly 3
- Queue `TYPE_CONTENT_RATED` with metadata: `{'milestone': 'rating_threshold', 'rating_count': 3}`

### 3. Database Changes

Add `milestones_reached` JSON column to `contents` table to track which milestones have been triggered:
```php
$table->json('milestones_reached')->nullable();
```

Structure:
```json
{
  "views": [10, 25, 50],
  "downloads": [1, 3, 5],
  "first_review": true,
  "rating_threshold": true
}
```

### 4. Implementation Files

#### 4.1 Create Milestone Service
**File:** `app/Services/MilestoneTrackerService.php`
- `checkViewsMilestone(Content $content): ?int`
- `checkDownloadsMilestone(Content $content): ?int`
- `checkRatingThreshold(Content $content): bool`
- `markMilestoneReached(Content $content, string $type, mixed $value): void`

#### 4.2 Update Event Listeners
**File:** `app/Listeners/QueueCrystalUpdateListener.php`

Add methods:
- `handleContentViewedMilestone(ContentViewedEvent $event): void`
- `handleContentDownloadedMilestone(ContentDownloadedEvent $event): void`
- `handleRatingThreshold(ContentRatedEvent $event): void`

Modify existing:
- `handleContentViewed()` - add milestone check
- `handleContentDownloaded()` - add milestone check
- `handleContentRated()` - add rating count check

#### 4.3 Create Invitation Observers
**File:** `app/Observers/InvitationObserver.php`

Events:
- `created()` - queue crystal update for inviter
- `updated()` - check if status changed to 'accepted', queue for both users

Register in `AppServiceProvider`:
```php
Invitation::observe(InvitationObserver::class);
```

#### 4.4 Database Migration
**File:** `database/migrations/YYYY_MM_DD_HHMMSS_add_milestones_to_contents.php`
```php
Schema::table('contents', function (Blueprint $table) {
    $table->json('milestones_reached')->nullable()->after('metadata');
});
```

### 5. Crystal Growth Impact

All milestone achievements queue crystal updates via `CrystalActivityQueue`. The existing `ProcessCrystalUpdatesCommand` and `RecalculateCrystalMetricsJob` handle the actual recalculation.

**Growth factors:**
- Invitation sent: Increases engagement_score
- Invitation accepted: Increases engagement_score (both users)
- Content milestones: Increases interaction_score (implicitly via views/downloads)
- Review milestone: Increases interaction_score
- Rating threshold: Increases interaction_score

### 6. Testing Requirements

**Unit Tests:**
- `MilestoneTrackerService` methods
- Milestone detection logic
- Duplicate milestone prevention

**Feature Tests:**
- Invitation creation triggers crystal update
- Invitation acceptance triggers crystal updates for both users
- View milestones trigger at correct thresholds
- Download milestones trigger at correct thresholds
- Rating threshold triggers at 3 ratings
- Milestones don't trigger twice

### 7. Configuration

Add to `config/crystals.php` (create if needed):
```php
return [
    'milestones' => [
        'views' => [10, 25, 50, 100, 250, 500, 1000, 2500, 5000, 10000],
        'downloads' => [1, 3, 5, 10, 25, 50, 100, 250, 500, 1000],
        'rating_threshold' => 3,
    ],
];
```

## Implementation Order

1. Create migration for `milestones_reached` column
2. Create `MilestoneTrackerService`
3. Create `InvitationObserver`
4. Update `QueueCrystalUpdateListener` with milestone checks
5. Add configuration file
6. Write tests
7. Run migration and test
