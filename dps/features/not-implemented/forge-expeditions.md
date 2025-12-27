# Forge Expeditions - Content Generation Challenges

## Overview

Timed content creation challenges that reward users with crystal growth multipliers and unique visual status. Expeditions guide creators toward specific themes or formats, promoting engagement through gamified goals and automated progress tracking.

## Database Schema

### expeditions Table
```php
Schema::create('expeditions', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('description');
    $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
    $table->timestamp('starts_at');
    $table->timestamp('ends_at');
    $table->json('requirements'); // Content criteria
    $table->json('rewards'); // Multipliers and bonuses
    $table->integer('max_participants')->nullable();
    $table->timestamps();
});

// Indexes
$table->index(['status', 'starts_at', 'ends_at']);
```

**Requirements JSON Structure:**
```json
{
  "content_type": "post",
  "min_word_count": 500,
  "required_count": 3,
  "tags": ["worldbuilding", "fantasy"],
  "categories": ["tutorial", "guide"]
}
```

**Rewards JSON Structure:**
```json
{
  "crystal_multiplier": 2.5,
  "engagement_bonus": 100,
  "interaction_bonus": 50,
  "visual_effect": "expedition_winner_aura",
  "effect_duration_days": 30
}
```

### expedition_enrollments Table
```php
Schema::create('expedition_enrollments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('expedition_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->timestamp('enrolled_at');
    $table->timestamp('completed_at')->nullable();
    $table->json('progress')->nullable(); // Progress tracking
    $table->boolean('reward_claimed')->default(false);
    $table->timestamps();

    $table->unique(['expedition_id', 'user_id']);
    $table->index(['user_id', 'completed_at']);
});
```

**Progress JSON Structure:**
```json
{
  "posts_created": 2,
  "total_required": 3,
  "qualifying_post_ids": [45, 67],
  "last_checked_at": "2025-12-27T10:30:00Z"
}
```

### expedition_qualifying_posts Table
```php
Schema::create('expedition_qualifying_posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('expedition_id')->constrained()->cascadeOnDelete();
    $table->foreignId('enrollment_id')->constrained('expedition_enrollments')->cascadeOnDelete();
    $table->foreignId('post_id')->constrained()->cascadeOnDelete();
    $table->timestamp('qualified_at');

    $table->unique(['expedition_id', 'post_id']);
    $table->index(['enrollment_id', 'qualified_at']);
});
```

### user_expedition_effects Table
```php
Schema::create('user_expedition_effects', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('expedition_id')->constrained()->cascadeOnDelete();
    $table->string('effect_type'); // 'expedition_winner_aura', 'crystal_surge', etc.
    $table->timestamp('activated_at');
    $table->timestamp('expires_at');
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index(['user_id', 'is_active', 'expires_at']);
});
```

## Models

### Expedition Model (`app/Models/Expedition.php`)

**Fillable Fields:**
- title, slug, description, status, starts_at, ends_at
- requirements, rewards, max_participants

**Casts:**
- starts_at: datetime
- ends_at: datetime
- requirements: array
- rewards: array

**Relationships:**
- `enrollments()`: HasMany ExpeditionEnrollment
- `participants()`: HasManyThrough User via enrollments
- `qualifyingPosts()`: HasManyThrough Post via expedition_qualifying_posts

**Scopes:**
- `active()`: status = 'active' AND now() between starts_at and ends_at
- `upcoming()`: status = 'active' AND starts_at > now()
- `completed()`: status = 'completed' OR ends_at < now()
- `enrollable()`: active() AND (max_participants IS NULL OR enrollments_count < max_participants)

**Methods:**
- `isActive()`: Check if expedition is currently running
- `isEnrollable()`: Check if users can still enroll
- `hasEnded()`: Check if expedition period has passed
- `getRemainingSlots()`: Calculate available enrollment slots
- `getParticipantCount()`: Count enrolled users
- `getCompletionRate()`: Calculate % of enrolled users who completed

**Auto-Slug Generation:**
- Creating: Auto-generates slug from title
- Ensures uniqueness with counter suffix

### ExpeditionEnrollment Model (`app/Models/ExpeditionEnrollment.php`)

**Fillable Fields:**
- expedition_id, user_id, enrolled_at, completed_at, progress, reward_claimed

**Casts:**
- enrolled_at: datetime
- completed_at: datetime
- progress: array
- reward_claimed: boolean

**Relationships:**
- `expedition()`: BelongsTo Expedition
- `user()`: BelongsTo User
- `qualifyingPosts()`: HasMany ExpeditionQualifyingPost

**Methods:**
- `isCompleted()`: Check if completed_at is set
- `getProgress()`: Return progress array or default structure
- `updateProgress(array $data)`: Update progress JSON
- `checkCompletion()`: Verify if requirements are met
- `claimReward()`: Process reward claiming

### ExpeditionQualifyingPost Model (`app/Models/ExpeditionQualifyingPost.php`)

**Fillable Fields:**
- expedition_id, enrollment_id, post_id, qualified_at

**Casts:**
- qualified_at: datetime

**Relationships:**
- `expedition()`: BelongsTo Expedition
- `enrollment()`: BelongsTo ExpeditionEnrollment
- `post()`: BelongsTo Post

### UserExpeditionEffect Model (`app/Models/UserExpeditionEffect.php`)

**Fillable Fields:**
- user_id, expedition_id, effect_type, activated_at, expires_at, is_active

**Casts:**
- activated_at: datetime
- expires_at: datetime
- is_active: boolean

**Relationships:**
- `user()`: BelongsTo User
- `expedition()`: BelongsTo Expedition

**Scopes:**
- `active()`: is_active = true AND expires_at > now()
- `expired()`: expires_at <= now() OR is_active = false

**Methods:**
- `isActive()`: Check if effect is currently active
- `deactivate()`: Set is_active to false
- `extend(int $days)`: Extend expires_at by N days

## User Model Extensions

Add to `app/Models/User.php`:

**Relationships:**
- `expeditionEnrollments()`: HasMany ExpeditionEnrollment
- `activeExpeditions()`: BelongsToMany Expedition via enrollments where not completed
- `completedExpeditions()`: BelongsToMany Expedition via enrollments where completed
- `expeditionEffects()`: HasMany UserExpeditionEffect

**Methods:**
- `enrollInExpedition(Expedition $expedition)`: Create enrollment record
- `isEnrolledIn(Expedition $expedition)`: Check enrollment status
- `getActiveExpeditionEffects()`: Return active visual effects
- `hasExpeditionWinnerAura()`: Check for winner aura effect

## Post Model Extensions

Add to `app/Models/Post.php`:

**Relationships:**
- `expeditionQualifications()`: HasMany ExpeditionQualifyingPost

**Methods:**
- `checkExpeditionQualification(Expedition $expedition)`: Verify if post meets requirements
- `getWordCount()`: Count words in content
- `qualifiesFor(Expedition $expedition)`: Boolean check against requirements

## Progress Tracking System

### ExpeditionProgressTracker Service (`app/Services/ExpeditionProgressTracker.php`)

**Purpose:** Automated progress checking when posts are published

**Methods:**
- `trackNewPost(Post $post)`: Check all active expeditions
- `checkPostAgainstExpedition(Post $post, Expedition $expedition)`: Validate requirements
- `updateEnrollmentProgress(ExpeditionEnrollment $enrollment, Post $post)`: Update progress JSON
- `checkAndCompleteEnrollment(ExpeditionEnrollment $enrollment)`: Verify completion criteria
- `processCompletion(ExpeditionEnrollment $enrollment)`: Grant rewards and effects

**Validation Logic:**
```php
// Check word count
$wordCount = $post->getWordCount();
if ($wordCount < $expedition->requirements['min_word_count']) {
    return false;
}

// Check tags (if specified)
if (isset($expedition->requirements['tags'])) {
    // Implement tag matching logic
}

// Check categories (if specified)
if (isset($expedition->requirements['categories'])) {
    // Implement category matching logic
}

return true;
```

### PostObserver Extension

Modify `app/Observers/PostObserver.php`:

**Add to `created()` method:**
```php
if ($post->status === 'published') {
    app(ExpeditionProgressTracker::class)->trackNewPost($post);
}
```

**Add to `updated()` method:**
```php
if ($post->isDirty('status') && $post->status === 'published') {
    app(ExpeditionProgressTracker::class)->trackNewPost($post);
}
```

## Reward System

### ExpeditionRewardService (`app/Services/ExpeditionRewardService.php`)

**Methods:**
- `grantRewards(ExpeditionEnrollment $enrollment)`: Process all rewards
- `applyCrystalMultiplier(User $user, float $multiplier)`: Update metrics
- `grantEngagementBonus(User $user, int $bonus)`: Queue crystal activity
- `activateVisualEffect(User $user, Expedition $expedition)`: Create effect record
- `sendCompletionNotification(User $user, Expedition $expedition)`: Notify user

**Crystal Integration:**
```php
// Queue special crystal activity for expedition completion
CrystalActivityQueue::addActivity(
    userId: $user->id,
    activityType: CrystalActivityQueue::TYPE_EXPEDITION_COMPLETED,
    metadata: [
        'expedition_id' => $expedition->id,
        'multiplier' => $rewards['crystal_multiplier'],
        'bonus_points' => $rewards['engagement_bonus']
    ]
);
```

**Visual Effect Activation:**
```php
UserExpeditionEffect::create([
    'user_id' => $user->id,
    'expedition_id' => $expedition->id,
    'effect_type' => $rewards['visual_effect'],
    'activated_at' => now(),
    'expires_at' => now()->addDays($rewards['effect_duration_days']),
    'is_active' => true
]);
```

## Filament Admin Resources

### ExpeditionResource (`app/Filament/Admin/Resources/ExpeditionResource.php`)

**Navigation:**
- Icon: heroicon-o-fire
- Group: Content
- Sort: 5

**Form Sections:**

1. **Basic Information**
   - Title (required, max 255)
   - Slug (auto-generated, unique)
   - Description (textarea, 5 rows)
   - Status (select: draft/active/completed/cancelled)

2. **Schedule**
   - Starts At (datetime picker, required)
   - Ends At (datetime picker, required, after starts_at)
   - Max Participants (numeric, nullable, min: 1)

3. **Requirements** (JSON Builder)
   - Content Type (select: post)
   - Min Word Count (numeric, default: 500)
   - Required Count (numeric, default: 3, label: "Number of posts required")
   - Tags (tags input, nullable)
   - Categories (select multiple, nullable)

4. **Rewards** (JSON Builder)
   - Crystal Multiplier (numeric, step: 0.1, default: 2.0, min: 1.0)
   - Engagement Bonus (numeric, default: 100)
   - Interaction Bonus (numeric, default: 50)
   - Visual Effect (select: expedition_winner_aura, crystal_surge, etc.)
   - Effect Duration (numeric, suffix: "days", default: 30)

**Table Columns:**
- Title (searchable, sortable)
- Status (badge: draft=gray, active=success, completed=info, cancelled=danger)
- Starts At (date, sortable)
- Ends At (date, sortable)
- Participants (count badge)
- Completions (progress bar: completed/enrolled)

**Filters:**
- Status (select filter)
- Active Now (toggle: checks if now() between starts/ends)
- Date Range (date range filter on starts_at)

**Actions:**
- View (slide-over modal with details)
- Edit
- Delete (with confirmation)
- Bulk Delete

**Custom Actions:**
- **Activate**: Change status to 'active' (only if draft)
- **Complete Early**: Change status to 'completed' and process all pending rewards
- **View Participants**: Navigate to filtered enrollment list

**Infolist (View Action):**
- Basic Info section (title, description, status)
- Schedule section (dates, duration, slots)
- Requirements section (formatted requirements JSON)
- Rewards section (formatted rewards JSON)
- Statistics section (enrollments, completions, completion rate)

### ExpeditionEnrollmentResource (`app/Filament/Admin/Resources/ExpeditionEnrollmentResource.php`)

**Navigation:**
- Icon: heroicon-o-users
- Group: Content
- Sort: 6
- Label: Expedition Participants

**Table Columns:**
- Expedition Title (sortable, searchable via relationship)
- User (anonymized name, searchable)
- Enrolled At (datetime, sortable)
- Progress (view component showing X/Y completed)
- Completed At (datetime, sortable, nullable)
- Reward Claimed (boolean icon)

**Filters:**
- Expedition (select filter)
- Completed (ternary: yes/no/all)
- Reward Claimed (ternary: yes/no/all)
- Enrollment Date (date range)

**Actions:**
- View Progress (slide-over modal)
- Force Complete (admin override, grants rewards)
- Remove Enrollment (with confirmation)

**Infolist (View Action):**
- User Info (anonymized name, Forge profile link)
- Expedition Info (title, dates, status)
- Progress Details (posts created vs required, qualifying posts list)
- Timestamps (enrolled, completed, reward claimed)
- Qualifying Posts table (mini table with post titles and dates)

## Frontend Components

### Dashboard Widget (`app/Filament/Admin/Widgets/ActiveExpeditionsWidget.php`)

**Purpose:** Display active expeditions on user dashboard

**Layout:**
- Table widget showing 5 most relevant expeditions
- Columns: Title, Ends In (human diff), Participants, Action button
- Action: "Enroll" button (if not enrolled) or "View Progress" link

**Filtering Logic:**
1. Active expeditions (status = active, within date range)
2. Not at max capacity
3. User not already enrolled
4. Ordered by starts_at DESC

### Public Expedition Page (`resources/views/expeditions/index.blade.php`)

**URL:** `/expeditions`

**Sections:**
1. **Hero**: "Active Expeditions - Forge Your Legend"
2. **Active Tab**: Current expeditions (cards with enroll buttons)
3. **Upcoming Tab**: Future expeditions (cards with countdown)
4. **Completed Tab**: Past expeditions (cards with winner counts)

**Expedition Card Component:**
- Title and description
- Duration badge (X days remaining)
- Participant count (X/Y enrolled)
- Requirements summary (3 posts, 500+ words, #worldbuilding)
- Rewards preview (2.5x crystal growth, Winner Aura)
- CTA button (Enroll Now / View Details / Completed)

### Expedition Detail Page (`resources/views/expeditions/show.blade.php`)

**URL:** `/expeditions/{slug}`

**Sections:**
1. **Header**: Title, status badge, dates, participant count
2. **Description**: Full description with formatting
3. **Requirements Panel**:
   - Content type and count
   - Word count requirement
   - Tag/category requirements
   - Visual checklist style
4. **Rewards Panel**:
   - Crystal multiplier highlight
   - Bonus points breakdown
   - Visual effect preview
   - Effect duration
5. **Your Progress** (if enrolled):
   - Progress bar (X/Y posts completed)
   - List of qualifying posts
   - Next steps guidance
6. **Leaderboard** (optional):
   - Top completers (anonymized)
   - Completion timestamps
7. **CTA**: Enroll button or completion status

### User Profile Integration

**Forge Profile Extension** (`resources/views/forge/profile.blade.php`):

Add "Expeditions" section showing:
- Active expedition enrollments with progress
- Completed expedition badges
- Active visual effects (winner aura, crystal surge)

**Activity Feed Integration:**
Add new activity types to `crystal_activity_queue` display:
- `expedition_enrolled` → 🚀 "Joined the {expedition_name} expedition"
- `expedition_completed` → 🏆 "Completed the {expedition_name} expedition"
- `expedition_effect_activated` → ✨ "Gained {effect_name} effect"

## Crystal Activity Queue Extensions

Add to `app/Models/CrystalActivityQueue.php`:

```php
public const TYPE_EXPEDITION_ENROLLED = 'expedition_enrolled';
public const TYPE_EXPEDITION_COMPLETED = 'expedition_completed';
public const TYPE_EXPEDITION_EFFECT_ACTIVATED = 'expedition_effect_activated';
```

Update `RecalculateCrystalMetricsJob` to handle expedition bonuses:
- Apply multiplier from active expedition completions
- Add bonus points to engagement_score
- Consider expedition effects in glow_intensity calculation

## Crystal Visual Effect System

### 3D Crystal Viewer Extension (`resources/js/components/CrystalViewer.js`)

**New Features:**
- `data-expedition-effects`: JSON array of active effects
- Effect renderers for each type:
  - `expedition_winner_aura`: Golden particle ring around crystal
  - `crystal_surge`: Pulsing glow animation
  - `spectral_shimmer`: Rainbow edge highlight

**Implementation:**
```javascript
if (expeditionEffects.includes('expedition_winner_aura')) {
    this.addWinnerAuraEffect();
}
```

## API Endpoints (Optional)

### Public API Routes (`routes/api.php`)

```php
// List active expeditions
GET /api/expeditions?status=active

// Get expedition details
GET /api/expeditions/{slug}

// Enroll in expedition (authenticated)
POST /api/expeditions/{slug}/enroll

// Get user's expedition progress (authenticated)
GET /api/user/expeditions

// Check post qualification (authenticated)
POST /api/expeditions/{slug}/check-post
```

## Artisan Commands

### ProcessExpeditionCompletionsCommand (`app/Console/Commands/ProcessExpeditionCompletionsCommand.php`)

**Signature:** `expedition:process-completions`

**Schedule:** Every 15 minutes

**Purpose:**
- Check all active enrollments for completion
- Grant rewards to newly completed enrollments
- Deactivate expired visual effects
- Update expedition statuses (active → completed if ended)

**Logic:**
```php
// Find enrollments that meet requirements but aren't marked complete
$pendingCompletions = ExpeditionEnrollment::whereNull('completed_at')
    ->whereHas('expedition', fn($q) => $q->active())
    ->get()
    ->filter(fn($e) => $e->checkCompletion());

foreach ($pendingCompletions as $enrollment) {
    app(ExpeditionRewardService::class)->grantRewards($enrollment);
}

// Deactivate expired effects
UserExpeditionEffect::where('is_active', true)
    ->where('expires_at', '<=', now())
    ->update(['is_active' => false]);

// Complete ended expeditions
Expedition::where('status', 'active')
    ->where('ends_at', '<', now())
    ->update(['status' => 'completed']);
```

### SeedExpeditionsCommand (`app/Console/Commands/SeedExpeditionsCommand.php`)

**Signature:** `expedition:seed {--count=3}`

**Purpose:** Create sample expeditions for testing

## Notifications

### ExpeditionEnrolledNotification

**Trigger:** User enrolls in expedition
**Channels:** Database, Mail (optional)
**Content:** Expedition details, requirements, end date

### ExpeditionProgressNotification

**Trigger:** User publishes qualifying post (25%, 50%, 75% milestones)
**Channels:** Database
**Content:** Progress update, posts remaining, encouragement

### ExpeditionCompletedNotification

**Trigger:** User completes expedition
**Channels:** Database, Mail
**Content:** Congratulations, rewards summary, visual effect info

### ExpeditionEndingSoonNotification

**Trigger:** 24 hours before expedition ends
**Channels:** Database, Mail (optional)
**Target:** Enrolled users who haven't completed
**Content:** Reminder, current progress, time remaining

## Testing Requirements

### Unit Tests

**ExpeditionTest:**
- `test_expedition_is_active_within_date_range()`
- `test_expedition_is_not_enrollable_when_full()`
- `test_expedition_calculates_remaining_slots()`
- `test_expedition_auto_generates_unique_slug()`

**ExpeditionEnrollmentTest:**
- `test_enrollment_tracks_progress_correctly()`
- `test_enrollment_detects_completion()`
- `test_enrollment_prevents_duplicate_qualifications()`
- `test_enrollment_claims_reward_only_once()`

**ExpeditionProgressTrackerTest:**
- `test_tracker_validates_word_count()`
- `test_tracker_validates_tags_and_categories()`
- `test_tracker_updates_enrollment_progress()`
- `test_tracker_triggers_completion_when_requirements_met()`

**ExpeditionRewardServiceTest:**
- `test_reward_service_grants_crystal_bonuses()`
- `test_reward_service_activates_visual_effects()`
- `test_reward_service_queues_crystal_activities()`
- `test_reward_service_sends_notifications()`

### Feature Tests

**ExpeditionEnrollmentTest:**
- `test_user_can_enroll_in_active_expedition()`
- `test_user_cannot_enroll_twice_in_same_expedition()`
- `test_user_cannot_enroll_in_full_expedition()`
- `test_user_cannot_enroll_in_expired_expedition()`

**ExpeditionProgressTest:**
- `test_publishing_qualifying_post_updates_progress()`
- `test_publishing_non_qualifying_post_does_not_update_progress()`
- `test_completing_expedition_grants_rewards()`
- `test_completing_expedition_activates_visual_effect()`

**ExpeditionApiTest:**
- `test_can_list_active_expeditions()`
- `test_can_view_expedition_details()`
- `test_can_enroll_when_authenticated()`
- `test_cannot_enroll_when_unauthenticated()`

**AdminExpeditionManagementTest:**
- `test_admin_can_create_expedition()`
- `test_admin_can_activate_expedition()`
- `test_admin_can_complete_expedition_early()`
- `test_admin_can_view_enrollment_list()`

## Configuration

Create `config/expeditions.php`:

```php
return [
    // Default expedition duration in days
    'default_duration_days' => 14,

    // Default max participants (null = unlimited)
    'default_max_participants' => null,

    // Minimum word count for posts
    'min_word_count' => 500,

    // Default crystal multiplier
    'default_crystal_multiplier' => 2.0,

    // Default reward bonuses
    'default_engagement_bonus' => 100,
    'default_interaction_bonus' => 50,

    // Visual effect durations (days)
    'effect_durations' => [
        'expedition_winner_aura' => 30,
        'crystal_surge' => 7,
        'spectral_shimmer' => 14,
    ],

    // Progress notification thresholds (%)
    'progress_notification_thresholds' => [25, 50, 75],

    // Reminder notification timing
    'ending_soon_hours' => 24,

    // Pagination
    'per_page' => 12,

    // Cache TTL (minutes)
    'cache_ttl' => 60,
];
```

## SEO Implementation

### Meta Tags (Expedition Pages)

**Index Page:**
- Title: "Active Expeditions – Forge Your Legend | Világműhely"
- Description: "Join timed content creation challenges and earn crystal growth multipliers. Explore active expeditions and forge your creator legacy."

**Detail Page:**
- Title: "{Expedition Title} – Expedition | Világműhely"
- Description: "{Expedition Description truncated to 160 chars}"
- OG Image: Dynamic expedition badge image

### Sitemap Integration

Modify `routes/web.php` sitemap route:

```php
// Add expedition routes
$activeExpeditions = Expedition::active()->get();
foreach ($activeExpeditions as $expedition) {
    $expeditionPages[] = [
        'loc' => route('expeditions.show', $expedition->slug),
        'lastmod' => $expedition->updated_at->toAtomString(),
        'changefreq' => 'daily',
        'priority' => '0.7',
    ];
}
```

## Performance Optimization

### Database Indexes
- `expeditions`: (status, starts_at, ends_at)
- `expedition_enrollments`: (user_id, completed_at), unique(expedition_id, user_id)
- `user_expedition_effects`: (user_id, is_active, expires_at)

### Caching Strategy
- Cache active expeditions list (60 min TTL)
- Cache expedition detail pages (30 min TTL)
- Cache user enrollment counts (15 min TTL)
- Eager load relationships: `with(['expedition', 'user', 'qualifyingPosts.post'])`

### Queue Jobs
- `ProcessExpeditionRewardsJob`: Async reward processing
- `SendExpeditionNotificationJob`: Async notifications
- `UpdateExpeditionStatisticsJob`: Batch statistics updates

## Security Considerations

### Authorization
- Only authenticated users can enroll
- Admin-only expedition management
- User can only view own enrollment progress
- Reward claiming requires completed enrollment verification

### Validation
- Expedition dates: ends_at must be after starts_at
- Max participants: must be positive integer or null
- Word count: must be positive integer
- Multipliers: must be >= 1.0
- Prevent enrollment in full/expired expeditions

### Rate Limiting
- Enrollment endpoint: 10 requests per minute
- API endpoints: 60 requests per minute
- Admin actions: 100 requests per minute

## Implementation Order

1. Create migrations (expeditions, enrollments, qualifying posts, effects)
2. Create models with relationships and methods
3. Create service classes (ProgressTracker, RewardService)
4. Extend Post observer for automatic tracking
5. Create Filament admin resources (Expedition, Enrollment)
6. Create dashboard widget
7. Create public routes and controllers
8. Create Blade views and components
9. Extend crystal viewer for visual effects
10. Create artisan commands and schedule
11. Create notification classes
12. Add configuration file
13. Write unit and feature tests
14. Update sitemap and SEO
15. Run migrations and seed sample data
16. Test complete enrollment → completion → reward flow

## Migration Checklist

- [ ] Run migrations: `php artisan migrate`
- [ ] Seed sample expeditions: `php artisan expedition:seed`
- [ ] Add scheduled command to `app/Console/Kernel.php`
- [ ] Build assets: `npm run build`
- [ ] Clear caches: `php artisan optimize:clear`
- [ ] Test expedition creation in admin
- [ ] Test user enrollment flow
- [ ] Test post publication triggers progress
- [ ] Test completion grants rewards
- [ ] Test visual effects display on Forge profile
- [ ] Verify sitemap includes expedition pages
- [ ] Test notifications are sent correctly
