# Crystal Resonator - Community Interaction System

## Overview

Social validation and community interaction system where users "resonate" with other creators' crystals, influencing visual appearance while earning personal growth. Uses existing `glow_intensity` and `purity_level` metrics to create meaningful daily social engagement.

## Database Schema

### user_resonances Table
```php
Schema::create('user_resonances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
    $table->timestamp('resonated_at');
    $table->boolean('is_active')->default(true); // For timed effects
    $table->timestamp('expires_at')->nullable(); // When glow boost expires
    $table->timestamps();

    $table->index(['receiver_id', 'is_active', 'expires_at']);
    $table->index(['sender_id', 'resonated_at']);
    $table->index(['resonated_at']);
});
```

### daily_resonance_pools Table
```php
Schema::create('daily_resonance_pools', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->date('date');
    $table->integer('charges_used')->default(0);
    $table->integer('max_charges')->default(5);
    $table->timestamp('last_reset_at')->nullable();
    $table->timestamps();

    $table->unique(['user_id', 'date']);
    $table->index(['date', 'user_id']);
});
```

### resonance_milestones Table
```php
Schema::create('resonance_milestones', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['sent', 'received']); // Sent or received resonances
    $table->integer('milestone_count'); // 10, 50, 100, 500, etc.
    $table->timestamp('achieved_at');
    $table->json('rewards')->nullable(); // Bonus rewards granted
    $table->timestamps();

    $table->unique(['user_id', 'type', 'milestone_count']);
    $table->index(['user_id', 'achieved_at']);
});
```

## Models

### UserResonance Model (`app/Models/UserResonance.php`)

**Fillable Fields:**
- sender_id, receiver_id, resonated_at, is_active, expires_at

**Casts:**
- resonated_at: datetime
- expires_at: datetime
- is_active: boolean

**Relationships:**
- `sender()`: BelongsTo User
- `receiver()`: BelongsTo User

**Scopes:**
- `active()`: is_active = true AND (expires_at IS NULL OR expires_at > now())
- `expired()`: expires_at <= now() OR is_active = false
- `sentBy(int $userId)`: sender_id = $userId
- `receivedBy(int $userId)`: receiver_id = $userId
- `recent(int $hours = 24)`: resonated_at >= now() - $hours

**Methods:**
- `isActive()`: Check if effect is still active
- `deactivate()`: Set is_active to false
- `getRemainingDuration()`: Calculate time until expiration (in hours)

**Constants:**
```php
public const EFFECT_DURATION_HOURS = 24; // How long glow boost lasts
public const GLOW_BOOST_AMOUNT = 0.05; // +5% glow per resonance
public const PURITY_REWARD_AMOUNT = 0.02; // +2% purity for sender
```

### DailyResonancePool Model (`app/Models/DailyResonancePool.php`)

**Fillable Fields:**
- user_id, date, charges_used, max_charges, last_reset_at

**Casts:**
- date: date
- last_reset_at: datetime
- charges_used: integer
- max_charges: integer

**Relationships:**
- `user()`: BelongsTo User

**Methods:**
- `hasChargesRemaining()`: charges_used < max_charges
- `getRemainingCharges()`: max_charges - charges_used
- `useCharge()`: Increment charges_used, return success boolean
- `resetPool()`: Set charges_used to 0, update last_reset_at
- `isExpired()`: Check if date is before today

**Static Methods:**
- `getTodayPool(int $userId)`: Get or create today's pool for user
- `resetExpiredPools()`: Reset all pools where date < today

**Constants:**
```php
public const DEFAULT_MAX_CHARGES = 5;
public const PREMIUM_MAX_CHARGES = 10; // Future: for premium users
```

### ResonanceMilestone Model (`app/Models/ResonanceMilestone.php`)

**Fillable Fields:**
- user_id, type, milestone_count, achieved_at, rewards

**Casts:**
- achieved_at: datetime
- rewards: array

**Relationships:**
- `user()`: BelongsTo User

**Constants:**
```php
public const MILESTONES_SENT = [10, 25, 50, 100, 250, 500, 1000];
public const MILESTONES_RECEIVED = [10, 25, 50, 100, 250, 500, 1000, 2500, 5000];
```

## User Model Extensions

Add to `app/Models/User.php`:

**Relationships:**
```php
/**
 * Resonances sent by this user
 */
public function resonancesSent(): HasMany
{
    return $this->hasMany(UserResonance::class, 'sender_id');
}

/**
 * Resonances received by this user
 */
public function resonancesReceived(): HasMany
{
    return $this->hasMany(UserResonance::class, 'receiver_id');
}

/**
 * Active resonances boosting this user's glow
 */
public function activeResonances(): HasMany
{
    return $this->resonancesReceived()->where('is_active', true)
        ->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
}

/**
 * Today's resonance pool
 */
public function todayResonancePool(): HasOne
{
    return $this->hasOne(DailyResonancePool::class)
        ->where('date', today());
}

/**
 * Resonance milestones achieved
 */
public function resonanceMilestones(): HasMany
{
    return $this->hasMany(ResonanceMilestone::class);
}
```

**Methods:**
```php
/**
 * Check if user can send resonance to another user
 */
public function canResonateWith(User $targetUser): bool
{
    // Cannot resonate with self
    if ($this->id === $targetUser->id) {
        return false;
    }

    // Check daily pool
    $pool = DailyResonancePool::getTodayPool($this->id);
    if (!$pool->hasChargesRemaining()) {
        return false;
    }

    // Check if already resonated today (optional: prevent spam)
    $alreadyResonatedToday = UserResonance::sentBy($this->id)
        ->receivedBy($targetUser->id)
        ->where('resonated_at', '>=', now()->startOfDay())
        ->exists();

    return !$alreadyResonatedToday;
}

/**
 * Send resonance to another user
 */
public function resonateWith(User $targetUser): bool
{
    if (!$this->canResonateWith($targetUser)) {
        return false;
    }

    DB::transaction(function() use ($targetUser) {
        // Create resonance record
        UserResonance::create([
            'sender_id' => $this->id,
            'receiver_id' => $targetUser->id,
            'resonated_at' => now(),
            'is_active' => true,
            'expires_at' => now()->addHours(UserResonance::EFFECT_DURATION_HOURS),
        ]);

        // Use charge from pool
        $pool = DailyResonancePool::getTodayPool($this->id);
        $pool->useCharge();

        // Queue crystal updates for both users
        CrystalActivityQueue::addActivity(
            userId: $targetUser->id,
            activityType: CrystalActivityQueue::TYPE_RESONANCE_RECEIVED,
            metadata: ['sender_id' => $this->id]
        );

        CrystalActivityQueue::addActivity(
            userId: $this->id,
            activityType: CrystalActivityQueue::TYPE_RESONANCE_SENT,
            metadata: ['receiver_id' => $targetUser->id]
        );

        // Check milestones
        app(ResonanceMilestoneService::class)->checkMilestones($this, 'sent');
        app(ResonanceMilestoneService::class)->checkMilestones($targetUser, 'received');
    });

    return true;
}

/**
 * Get remaining resonance charges for today
 */
public function getRemainingResonanceCharges(): int
{
    $pool = DailyResonancePool::getTodayPool($this->id);
    return $pool->getRemainingCharges();
}

/**
 * Get total active resonance boost (for display)
 */
public function getActiveResonanceCount(): int
{
    return $this->activeResonances()->count();
}

/**
 * Calculate current glow boost from active resonances
 */
public function calculateResonanceGlowBoost(): float
{
    $activeCount = $this->getActiveResonanceCount();
    $boostPerResonance = UserResonance::GLOW_BOOST_AMOUNT;
    $maxBoost = 0.25; // Cap at +25% glow

    return min($activeCount * $boostPerResonance, $maxBoost);
}
```

## Crystal Activity Queue Extensions

Add to `app/Models/CrystalActivityQueue.php`:

```php
public const TYPE_RESONANCE_SENT = 'resonance_sent';
public const TYPE_RESONANCE_RECEIVED = 'resonance_received';
public const TYPE_RESONANCE_MILESTONE = 'resonance_milestone';
```

## Crystal Metrics Calculation Integration

Modify `app/Jobs/RecalculateCrystalMetricsJob.php`:

**In `handle()` method, update glow_intensity calculation:**
```php
// Existing base glow calculation
$baseGlow = $this->calculateBaseGlow($user);

// NEW: Add resonance boost
$resonanceBoost = $user->calculateResonanceGlowBoost();
$finalGlow = min($baseGlow + $resonanceBoost, 1.0); // Cap at 1.0

// Update metric
$metric->glow_intensity = $finalGlow;
```

**In `handle()` method, update purity_level calculation:**
```php
// Existing base purity calculation
$basePurity = $this->calculateBasePurity($user);

// NEW: Add purity from sending resonances (lifetime count)
$sentResonancesCount = $user->resonancesSent()->count();
$purityBonus = min($sentResonancesCount * UserResonance::PURITY_REWARD_AMOUNT, 0.20); // Cap at +20%

$finalPurity = min($basePurity + $purityBonus, 1.0); // Cap at 1.0

// Update metric
$metric->purity_level = $finalPurity;
```

## Services

### ResonanceMilestoneService (`app/Services/ResonanceMilestoneService.php`)

**Methods:**

```php
/**
 * Check and award milestones for a user
 */
public function checkMilestones(User $user, string $type): void
{
    $count = $this->getResonanceCount($user, $type);
    $milestones = $type === 'sent'
        ? ResonanceMilestone::MILESTONES_SENT
        : ResonanceMilestone::MILESTONES_RECEIVED;

    foreach ($milestones as $milestone) {
        if ($count >= $milestone) {
            $this->awardMilestone($user, $type, $milestone);
        }
    }
}

/**
 * Get total resonance count for user
 */
protected function getResonanceCount(User $user, string $type): int
{
    return $type === 'sent'
        ? $user->resonancesSent()->count()
        : $user->resonancesReceived()->count();
}

/**
 * Award milestone if not already achieved
 */
protected function awardMilestone(User $user, string $type, int $count): void
{
    $exists = ResonanceMilestone::where('user_id', $user->id)
        ->where('type', $type)
        ->where('milestone_count', $count)
        ->exists();

    if ($exists) {
        return;
    }

    $rewards = $this->calculateRewards($count);

    ResonanceMilestone::create([
        'user_id' => $user->id,
        'type' => $type,
        'milestone_count' => $count,
        'achieved_at' => now(),
        'rewards' => $rewards,
    ]);

    // Queue crystal activity for milestone
    CrystalActivityQueue::addActivity(
        userId: $user->id,
        activityType: CrystalActivityQueue::TYPE_RESONANCE_MILESTONE,
        metadata: [
            'type' => $type,
            'milestone' => $count,
            'rewards' => $rewards,
        ]
    );

    // Send notification
    $user->notify(new ResonanceMilestoneAchievedNotification($type, $count, $rewards));
}

/**
 * Calculate rewards for milestone
 */
protected function calculateRewards(int $milestoneCount): array
{
    $baseBonus = match($milestoneCount) {
        10 => 50,
        25 => 100,
        50 => 200,
        100 => 400,
        250 => 750,
        500 => 1500,
        1000 => 3000,
        2500 => 6000,
        5000 => 10000,
        default => 0,
    };

    return [
        'engagement_bonus' => $baseBonus,
        'title' => $this->getMilestoneTitle($milestoneCount),
    ];
}

/**
 * Get display title for milestone
 */
protected function getMilestoneTitle(int $count): string
{
    return match($count) {
        10 => 'Echo Initiate',
        25 => 'Soul Whisperer',
        50 => 'Harmony Seeker',
        100 => 'Resonance Adept',
        250 => 'Crystal Harmonizer',
        500 => 'Echo Master',
        1000 => 'Resonance Sage',
        2500 => 'Soul Symphony',
        5000 => 'Eternal Resonator',
        default => "Resonance Level {$count}",
    };
}
```

### ResonanceNotificationService (`app/Services/ResonanceNotificationService.php`)

**Methods:**

```php
/**
 * Notify user when they receive a resonance
 */
public function notifyResonanceReceived(User $receiver, User $sender): void
{
    // Only notify if user has notification preference enabled
    if (!$this->shouldNotify($receiver, 'resonance_received')) {
        return;
    }

    $receiver->notify(new ResonanceReceivedNotification($sender));
}

/**
 * Notify user about daily pool reset
 */
public function notifyPoolRefilled(User $user): void
{
    if (!$this->shouldNotify($user, 'resonance_pool_refilled')) {
        return;
    }

    $user->notify(new ResonancePoolRefilledNotification());
}

/**
 * Check notification preferences
 */
protected function shouldNotify(User $user, string $type): bool
{
    $preferences = $user->notification_preferences ?? [];
    return $preferences[$type] ?? true;
}
```

## Controllers

### ResonanceController (`app/Http/Controllers/ResonanceController.php`)

**Routes:**
```php
Route::middleware('auth')->group(function() {
    Route::post('/resonance/{user}', [ResonanceController::class, 'store'])->name('resonance.store');
    Route::get('/resonance/pool', [ResonanceController::class, 'pool'])->name('resonance.pool');
    Route::get('/resonance/history', [ResonanceController::class, 'history'])->name('resonance.history');
});
```

**Methods:**

```php
/**
 * Send resonance to a user
 */
public function store(User $targetUser)
{
    $user = auth()->user();

    if (!$user->canResonateWith($targetUser)) {
        return response()->json([
            'success' => false,
            'message' => 'Cannot resonate with this user at this time.',
            'remaining_charges' => $user->getRemainingResonanceCharges(),
        ], 422);
    }

    $success = $user->resonateWith($targetUser);

    if ($success) {
        // Send notification
        app(ResonanceNotificationService::class)
            ->notifyResonanceReceived($targetUser, $user);

        return response()->json([
            'success' => true,
            'message' => 'Resonance sent successfully!',
            'remaining_charges' => $user->getRemainingResonanceCharges(),
            'active_boost_count' => $targetUser->getActiveResonanceCount(),
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Failed to send resonance.',
    ], 500);
}

/**
 * Get current resonance pool status
 */
public function pool()
{
    $user = auth()->user();
    $pool = DailyResonancePool::getTodayPool($user->id);

    return response()->json([
        'remaining_charges' => $pool->getRemainingCharges(),
        'max_charges' => $pool->max_charges,
        'used_charges' => $pool->charges_used,
        'resets_at' => now()->endOfDay()->toIso8601String(),
    ]);
}

/**
 * Get resonance history (sent and received)
 */
public function history()
{
    $user = auth()->user();

    $sent = $user->resonancesSent()
        ->with('receiver')
        ->latest('resonated_at')
        ->take(20)
        ->get();

    $received = $user->resonancesReceived()
        ->with('sender')
        ->latest('resonated_at')
        ->take(20)
        ->get();

    return response()->json([
        'sent' => $sent,
        'received' => $received,
        'total_sent' => $user->resonancesSent()->count(),
        'total_received' => $user->resonancesReceived()->count(),
        'active_boost_count' => $user->getActiveResonanceCount(),
    ]);
}
```

## Frontend Components

### Resonance Button Component (`resources/views/components/resonance-button.blade.php`)

**Usage:** Include on Forge profiles and gallery cards

```blade
@props(['user'])

<div x-data="resonanceButton({{ $user->id }})" class="resonance-button-wrapper">
    <button
        @click="sendResonance"
        :disabled="!canResonate || isLoading"
        class="resonance-btn"
        :class="{ 'resonating': isResonating }"
    >
        <svg class="resonance-icon" :class="{ 'pulse': isResonating }">
            <!-- Wave/energy icon -->
        </svg>
        <span x-text="buttonText"></span>
    </button>

    <div class="resonance-pool-indicator" x-show="showPool">
        <span x-text="`${remainingCharges}/${maxCharges} charges`"></span>
    </div>
</div>

@push('scripts')
<script>
function resonanceButton(targetUserId) {
    return {
        targetUserId,
        canResonate: true,
        isLoading: false,
        isResonating: false,
        remainingCharges: 5,
        maxCharges: 5,
        showPool: false,
        buttonText: 'Resonate',

        async init() {
            await this.fetchPoolStatus();
        },

        async fetchPoolStatus() {
            try {
                const response = await fetch('/resonance/pool');
                const data = await response.json();
                this.remainingCharges = data.remaining_charges;
                this.maxCharges = data.max_charges;
                this.canResonate = this.remainingCharges > 0;
            } catch (error) {
                console.error('Failed to fetch pool status:', error);
            }
        },

        async sendResonance() {
            if (!this.canResonate || this.isLoading) return;

            this.isLoading = true;
            this.isResonating = true;

            try {
                const response = await fetch(`/resonance/${this.targetUserId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.remainingCharges = data.remaining_charges;
                    this.showSuccessAnimation();
                    this.showNotification('Resonance sent! ✨', 'success');
                } else {
                    this.showNotification(data.message, 'error');
                }
            } catch (error) {
                this.showNotification('Failed to send resonance', 'error');
            } finally {
                this.isLoading = false;
                setTimeout(() => {
                    this.isResonating = false;
                }, 2000);
            }
        },

        showSuccessAnimation() {
            // Trigger particle effect animation
            this.$dispatch('resonance-sent', { targetUserId: this.targetUserId });
        },

        showNotification(message, type) {
            // Integrate with existing notification system
        }
    };
}
</script>
@endpush
```

### Resonance Pool Widget (`resources/views/components/resonance-pool-widget.blade.php`)

**Usage:** Dashboard widget showing daily pool status

```blade
<div x-data="resonancePoolWidget" class="resonance-pool-widget">
    <div class="widget-header">
        <h3>Resonance Pool</h3>
        <span class="reset-timer" x-text="resetTime"></span>
    </div>

    <div class="pool-visualization">
        <template x-for="i in maxCharges" :key="i">
            <div
                class="charge-orb"
                :class="{ 'used': i > remainingCharges, 'available': i <= remainingCharges }"
            ></div>
        </template>
    </div>

    <div class="pool-stats">
        <span x-text="`${remainingCharges}/${maxCharges} available`"></span>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('resonancePoolWidget', () => ({
        remainingCharges: 5,
        maxCharges: 5,
        resetsAt: null,
        resetTime: '',

        async init() {
            await this.fetchPoolStatus();
            this.updateResetTimer();
            setInterval(() => this.updateResetTimer(), 60000); // Update every minute
        },

        async fetchPoolStatus() {
            const response = await fetch('/resonance/pool');
            const data = await response.json();
            this.remainingCharges = data.remaining_charges;
            this.maxCharges = data.max_charges;
            this.resetsAt = new Date(data.resets_at);
        },

        updateResetTimer() {
            const now = new Date();
            const diff = this.resetsAt - now;
            const hours = Math.floor(diff / 3600000);
            const minutes = Math.floor((diff % 3600000) / 60000);
            this.resetTime = `Resets in ${hours}h ${minutes}m`;
        }
    }));
});
</script>
@endpush
```

### Active Resonances Display (Forge Profile)

Add to `resources/views/forge/profile.blade.php`:

```blade
@if($user->getActiveResonanceCount() > 0)
<div class="active-resonances-badge">
    <svg class="pulse-icon"><!-- Energy icon --></svg>
    <span>{{ $user->getActiveResonanceCount() }} Active Resonances</span>
    <span class="glow-boost">+{{ round($user->calculateResonanceGlowBoost() * 100) }}% Glow</span>
</div>
@endif
```

### Resonance History Tab (Forge Profile)

Add new tab to portfolio sections:

```blade
<div x-show="activeTab === 'resonances'" class="resonance-history">
    <div class="history-section">
        <h3>💫 Received Resonances</h3>
        <div class="resonance-list">
            @foreach($recentResonancesReceived as $resonance)
            <div class="resonance-item">
                <a href="{{ route('forge.profile', $resonance->sender->username) }}">
                    {{ $resonance->sender->anonymized_name }}
                </a>
                <span class="timestamp">{{ $resonance->resonated_at->diffForHumans() }}</span>
                @if($resonance->isActive())
                    <span class="active-badge">Active</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <div class="history-section">
        <h3>✨ Sent Resonances</h3>
        <div class="resonance-list">
            @foreach($recentResonancesSent as $resonance)
            <div class="resonance-item">
                <a href="{{ route('forge.profile', $resonance->receiver->username) }}">
                    {{ $resonance->receiver->anonymized_name }}
                </a>
                <span class="timestamp">{{ $resonance->resonated_at->diffForHumans() }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="milestone-badges">
        <h3>🏆 Resonance Milestones</h3>
        <div class="badges-grid">
            @foreach($user->resonanceMilestones as $milestone)
            <div class="milestone-badge">
                <span class="badge-title">{{ $milestone->rewards['title'] }}</span>
                <span class="badge-count">{{ $milestone->milestone_count }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
```

## Filament Admin Resources

### UserResonanceResource (`app/Filament/Admin/Resources/UserResonanceResource.php`)

**Navigation:**
- Icon: heroicon-o-sparkles
- Group: Community
- Sort: 10
- Label: Resonances

**Table Columns:**
- Sender (anonymized name, searchable)
- Receiver (anonymized name, searchable)
- Resonated At (datetime, sortable)
- Is Active (boolean icon)
- Expires At (datetime, sortable)
- Duration (calculated: expires_at - resonated_at)

**Filters:**
- Active Status (ternary: active/expired/all)
- Date Range (resonated_at)
- Sender (select filter)
- Receiver (select filter)

**Actions:**
- View (slide-over modal)
- Deactivate (admin override)
- Delete (with confirmation)

**Infolist:**
- Sender Info (name, Forge link)
- Receiver Info (name, Forge link)
- Status (active/expired with badge)
- Timestamps (resonated, expires)
- Effect Details (glow boost amount, remaining duration)

**Widgets:**
- Stats Overview: Total resonances today, active resonances, unique participants

### DailyResonancePoolResource (`app/Filament/Admin/Resources/DailyResonancePoolResource.php`)

**Navigation:**
- Icon: heroicon-o-beaker
- Group: Community
- Sort: 11
- Label: Resonance Pools

**Table Columns:**
- User (anonymized name, searchable)
- Date (date, sortable)
- Charges Used / Max Charges (progress bar)
- Last Reset At (datetime)
- Remaining (calculated column)

**Filters:**
- Date (date filter)
- Usage Level (select: empty, partial, full)

**Actions:**
- View
- Reset Pool (admin action to refill charges)
- Adjust Max Charges (admin action)

**Bulk Actions:**
- Reset Selected Pools
- Delete Old Pools (older than 30 days)

### ResonanceMilestoneResource (`app/Filament/Admin/Resources/ResonanceMilestoneResource.php`)

**Navigation:**
- Icon: heroicon-o-trophy
- Group: Community
- Sort: 12
- Label: Resonance Milestones

**Table Columns:**
- User (anonymized name, searchable)
- Type (badge: sent=blue, received=green)
- Milestone Count (sortable)
- Title (from rewards JSON)
- Achieved At (datetime, sortable)
- Bonus Awarded (from rewards JSON)

**Filters:**
- Type (select: sent/received)
- Milestone Tier (select: 10/25/50/100/etc.)
- Achievement Date (date range)

**Actions:**
- View Achievement Details
- Revoke Milestone (admin action with confirmation)

## Artisan Commands

### ResetDailyResonancePoolsCommand (`app/Console/Commands/ResetDailyResonancePoolsCommand.php`)

**Signature:** `resonance:reset-pools`

**Schedule:** Daily at midnight

**Purpose:**
- Reset all expired pools (date < today)
- Create new pools for active users
- Clean up old pool records (older than 30 days)
- Send refill notifications to users

```php
public function handle()
{
    $resetCount = DailyResonancePool::where('date', '<', today())
        ->update([
            'charges_used' => 0,
            'last_reset_at' => now(),
            'date' => today(),
        ]);

    $this->info("Reset {$resetCount} resonance pools");

    // Clean up old records
    $deletedCount = DailyResonancePool::where('date', '<', today()->subDays(30))
        ->delete();

    $this->info("Deleted {$deletedCount} old pool records");

    return self::SUCCESS;
}
```

### ExpireResonancesCommand (`app/Console/Commands/ExpireResonancesCommand.php`)

**Signature:** `resonance:expire`

**Schedule:** Every 15 minutes

**Purpose:**
- Deactivate expired resonances
- Queue crystal recalculations for affected users
- Clean up old resonance records

```php
public function handle()
{
    $expiredResonances = UserResonance::where('is_active', true)
        ->where('expires_at', '<=', now())
        ->get();

    foreach ($expiredResonances as $resonance) {
        $resonance->deactivate();

        // Queue crystal update for receiver (glow will decrease)
        CrystalActivityQueue::addActivity(
            userId: $resonance->receiver_id,
            activityType: CrystalActivityQueue::TYPE_RESONANCE_EXPIRED,
            metadata: ['sender_id' => $resonance->sender_id]
        );
    }

    $this->info("Expired {$expiredResonances->count()} resonances");

    return self::SUCCESS;
}
```

### SeedResonanceMilestonesCommand (`app/Console/Commands/SeedResonanceMilestonesCommand.php`)

**Signature:** `resonance:seed-milestones {user_id?}`

**Purpose:** Backfill milestones for existing users or specific user

```php
public function handle()
{
    $userId = $this->argument('user_id');
    $users = $userId ? User::where('id', $userId)->get() : User::all();

    foreach ($users as $user) {
        app(ResonanceMilestoneService::class)->checkMilestones($user, 'sent');
        app(ResonanceMilestoneService::class)->checkMilestones($user, 'received');
    }

    $this->info("Seeded milestones for {$users->count()} users");

    return self::SUCCESS;
}
```

## Notifications

### ResonanceReceivedNotification (`app/Notifications/ResonanceReceivedNotification.php`)

**Channels:** Database, Mail (optional)

**Content:**
```php
public function toArray($notifiable)
{
    return [
        'title' => 'Crystal Resonance Received! ✨',
        'message' => "{$this->sender->anonymized_name} resonated with your crystal",
        'sender_id' => $this->sender->id,
        'sender_name' => $this->sender->anonymized_name,
        'active_boost_count' => $notifiable->getActiveResonanceCount(),
        'action_url' => route('forge.profile', $this->sender->username),
    ];
}
```

### ResonanceMilestoneAchievedNotification

**Channels:** Database, Mail

**Content:**
```php
public function toArray($notifiable)
{
    return [
        'title' => 'Resonance Milestone Achieved! 🏆',
        'message' => "You've reached {$this->milestoneCount} {$this->type} resonances!",
        'milestone_title' => $this->rewards['title'],
        'bonus_awarded' => $this->rewards['engagement_bonus'],
        'type' => $this->type,
        'count' => $this->milestoneCount,
    ];
}
```

### ResonancePoolRefilledNotification

**Channels:** Database

**Content:**
```php
public function toArray($notifiable)
{
    return [
        'title' => 'Resonance Pool Refilled! 🔋',
        'message' => 'Your daily resonance charges have been restored',
        'charges_available' => DailyResonancePool::DEFAULT_MAX_CHARGES,
    ];
}
```

## Activity Feed Integration

Update activity feed on Forge profiles to display:

```php
// In ForgeController@show method
$recentActivities = CrystalActivityQueue::forUser($user->id)
    ->latest('created_at')
    ->take(20)
    ->get()
    ->map(function($activity) {
        return [
            'icon' => $this->getActivityIcon($activity->activity_type),
            'message' => $this->getActivityMessage($activity),
            'timestamp' => $activity->created_at,
        ];
    });

// Add activity type handlers
protected function getActivityMessage($activity): string
{
    return match($activity->activity_type) {
        CrystalActivityQueue::TYPE_RESONANCE_SENT =>
            "Sent resonance to Creator #{$activity->metadata['receiver_id']}",
        CrystalActivityQueue::TYPE_RESONANCE_RECEIVED =>
            "Received resonance from Creator #{$activity->metadata['sender_id']}",
        CrystalActivityQueue::TYPE_RESONANCE_MILESTONE =>
            "Achieved milestone: {$activity->metadata['rewards']['title']}",
        // ... existing types
    };
}

protected function getActivityIcon($type): string
{
    return match($type) {
        CrystalActivityQueue::TYPE_RESONANCE_SENT => '✨',
        CrystalActivityQueue::TYPE_RESONANCE_RECEIVED => '💫',
        CrystalActivityQueue::TYPE_RESONANCE_MILESTONE => '🏆',
        // ... existing types
    };
}
```

## 3D Crystal Visual Effects

### Glow Intensity Boost Visualization

Modify `resources/js/components/CrystalViewer.js`:

```javascript
// Add resonance data attribute
const resonanceBoost = parseFloat(this.container.dataset.resonanceBoost || 0);
const activeResonances = parseInt(this.container.dataset.activeResonances || 0);

// Apply to glow intensity
const baseGlow = this.glowIntensity;
const boostedGlow = Math.min(baseGlow + resonanceBoost, 1.0);

// Update material emissive intensity
this.crystalMaterial.emissiveIntensity = boostedGlow;

// Add particle effect for active resonances
if (activeResonances > 0) {
    this.addResonanceParticles(activeResonances);
}

// Particle effect method
addResonanceParticles(count) {
    const particleGeometry = new THREE.BufferGeometry();
    const particleCount = count * 10; // 10 particles per resonance
    const positions = new Float32Array(particleCount * 3);

    for (let i = 0; i < particleCount; i++) {
        const angle = (i / particleCount) * Math.PI * 2;
        const radius = 1.5 + Math.random() * 0.5;
        positions[i * 3] = Math.cos(angle) * radius;
        positions[i * 3 + 1] = (Math.random() - 0.5) * 2;
        positions[i * 3 + 2] = Math.sin(angle) * radius;
    }

    particleGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

    const particleMaterial = new THREE.PointsMaterial({
        color: 0xffd700, // Golden particles
        size: 0.05,
        transparent: true,
        opacity: 0.6,
        blending: THREE.AdditiveBlending
    });

    const particles = new THREE.Points(particleGeometry, particleMaterial);
    this.scene.add(particles);

    // Animate particles orbiting
    this.resonanceParticles = particles;
}

// In animation loop
if (this.resonanceParticles) {
    this.resonanceParticles.rotation.y += 0.002;
}
```

## Testing Requirements

### Unit Tests

**UserResonanceTest:**
- `test_resonance_has_correct_relationships()`
- `test_resonance_is_active_within_expiry()`
- `test_resonance_expires_after_duration()`
- `test_can_calculate_remaining_duration()`

**DailyResonancePoolTest:**
- `test_pool_tracks_charges_correctly()`
- `test_pool_prevents_overuse()`
- `test_pool_resets_daily()`
- `test_pool_calculates_remaining_charges()`

**ResonanceMilestoneServiceTest:**
- `test_service_detects_milestones()`
- `test_service_awards_rewards()`
- `test_service_prevents_duplicate_awards()`
- `test_service_calculates_correct_titles()`

**UserModelTest:**
- `test_user_can_resonate_with_another_user()`
- `test_user_cannot_resonate_with_self()`
- `test_user_cannot_resonate_without_charges()`
- `test_user_cannot_resonate_twice_per_day()`
- `test_user_calculates_glow_boost_correctly()`

### Feature Tests

**ResonanceFlowTest:**
- `test_sending_resonance_creates_record()`
- `test_sending_resonance_uses_charge()`
- `test_sending_resonance_updates_crystals()`
- `test_receiving_resonance_increases_glow()`
- `test_sending_resonance_increases_purity()`
- `test_resonance_expires_after_duration()`

**ResonanceMilestoneTest:**
- `test_milestone_awarded_at_threshold()`
- `test_milestone_queues_crystal_activity()`
- `test_milestone_sends_notification()`
- `test_milestone_not_awarded_twice()`

**ResonanceApiTest:**
- `test_authenticated_user_can_send_resonance()`
- `test_unauthenticated_user_cannot_send_resonance()`
- `test_api_returns_correct_pool_status()`
- `test_api_returns_resonance_history()`
- `test_api_validates_daily_limit()`

**CrystalMetricsIntegrationTest:**
- `test_crystal_metrics_include_resonance_boost()`
- `test_glow_intensity_increases_with_active_resonances()`
- `test_purity_level_increases_with_sent_resonances()`
- `test_expired_resonances_decrease_glow()`

## Configuration

Create `config/resonance.php`:

```php
return [
    // Daily pool settings
    'daily_pool' => [
        'default_charges' => 5,
        'premium_charges' => 10, // For future premium users
        'reset_time' => '00:00', // Midnight
    ],

    // Resonance effect settings
    'effects' => [
        'duration_hours' => 24,
        'glow_boost_per_resonance' => 0.05, // 5% per resonance
        'max_glow_boost' => 0.25, // Cap at 25%
        'purity_reward_per_sent' => 0.02, // 2% per sent resonance
        'max_purity_bonus' => 0.20, // Cap at 20%
    ],

    // Milestone settings
    'milestones' => [
        'sent' => [10, 25, 50, 100, 250, 500, 1000],
        'received' => [10, 25, 50, 100, 250, 500, 1000, 2500, 5000],
        'bonus_multiplier' => 1.0, // Base bonus multiplier
    ],

    // Restrictions
    'restrictions' => [
        'prevent_same_day_duplicate' => true, // Can't resonate with same user twice per day
        'require_verified_email' => true, // Only verified users can send resonances
        'min_account_age_days' => 1, // Account must be X days old to send resonances
    ],

    // Cleanup settings
    'cleanup' => [
        'delete_old_pools_after_days' => 30,
        'delete_old_resonances_after_days' => 90,
    ],

    // Notification settings
    'notifications' => [
        'send_pool_refill_notification' => false,
        'send_milestone_notification' => true,
        'send_resonance_received_notification' => true,
    ],
];
```

## Rate Limiting

Add to `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ... existing
    'resonance.throttle' => \App\Http\Middleware\ResonanceThrottle::class,
];
```

Create `app/Http/Middleware/ResonanceThrottle.php`:

```php
public function handle($request, Closure $next)
{
    $user = $request->user();

    // 10 resonances per minute max
    $key = 'resonance_throttle:' . $user->id;
    $attempts = Cache::get($key, 0);

    if ($attempts >= 10) {
        return response()->json([
            'success' => false,
            'message' => 'Too many resonances. Please slow down.',
        ], 429);
    }

    Cache::put($key, $attempts + 1, 60);

    return $next($request);
}
```

Apply to routes:

```php
Route::post('/resonance/{user}', [ResonanceController::class, 'store'])
    ->middleware(['auth', 'resonance.throttle'])
    ->name('resonance.store');
```

## Performance Optimization

### Database Indexes
- `user_resonances`: (receiver_id, is_active, expires_at), (sender_id, resonated_at)
- `daily_resonance_pools`: unique(user_id, date), (date, user_id)
- `resonance_milestones`: unique(user_id, type, milestone_count)

### Caching Strategy
- Cache active resonance counts per user (5 min TTL)
- Cache daily pool status (1 min TTL)
- Cache milestone progress (15 min TTL)
- Use Redis for rate limiting

### Query Optimization
- Eager load sender/receiver relationships
- Use query scopes for common filters
- Index frequently queried columns
- Use batch processing for expiration checks

## Security Considerations

### Authorization
- Only authenticated users can send resonances
- Users cannot resonate with themselves
- Respect daily pool limits
- Validate target user exists and is active

### Validation
- CSRF protection on all POST requests
- Rate limiting on API endpoints
- Validate user IDs in requests
- Check account age and verification status

### Privacy
- Use anonymized names in all displays
- Don't expose personal user data
- Respect notification preferences
- Allow users to hide resonance history (optional)

## Implementation Order

1. Create migrations (user_resonances, daily_resonance_pools, resonance_milestones)
2. Create models with relationships and scopes
3. Extend User model with resonance methods
4. Create services (ResonanceMilestoneService, ResonanceNotificationService)
5. Update CrystalActivityQueue constants
6. Modify RecalculateCrystalMetricsJob for resonance boosts
7. Create ResonanceController and routes
8. Create Blade components (button, pool widget)
9. Update Forge profile with resonance display
10. Create Filament admin resources
11. Create artisan commands and schedule
12. Create notification classes
13. Add configuration file
14. Update crystal viewer JS for visual effects
15. Write unit and feature tests
16. Add rate limiting middleware
17. Run migrations
18. Test complete resonance flow

## Migration Checklist

- [ ] Run migrations: `php artisan migrate`
- [ ] Seed initial pools for existing users
- [ ] Add scheduled commands to `app/Console/Kernel.php`
- [ ] Build assets: `npm run build`
- [ ] Clear caches: `php artisan optimize:clear`
- [ ] Test resonance button on Forge profiles
- [ ] Test daily pool limits
- [ ] Test resonance expiration after 24 hours
- [ ] Test milestone awards
- [ ] Test glow boost on crystal viewer
- [ ] Test purity increase from sent resonances
- [ ] Verify notifications are sent
- [ ] Test admin resources
- [ ] Test rate limiting
- [ ] Verify crystal recalculation includes resonance effects
