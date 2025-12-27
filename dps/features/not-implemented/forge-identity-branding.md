# Forge Identity & Personal Branding System

## Overview

Identity control system allowing creators to choose between anonymous display ("Creator #{id}") and public personal branding (username), with dynamic SEO optimization, URL handling, and privacy safeguards.

## Epic Statement

**As a Creator, I want to control how my identity is displayed on my public Forge profile so that I can choose between absolute privacy and building a recognizable personal brand for social sharing.**

## Database Schema

### Users Table Extension

Add `display_mode` column to control identity visibility:

```php
Schema::table('users', function (Blueprint $table) {
    $table->enum('display_mode', ['anonymous', 'public'])->default('anonymous')->after('username');
    $table->timestamp('display_mode_changed_at')->nullable()->after('display_mode');
    $table->index('display_mode');
});
```

### Profile URL History Table

Create table to track username changes and enable redirects:

```php
Schema::create('user_url_history', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('old_username')->index();
    $table->string('new_username')->index();
    $table->timestamp('changed_at');
    $table->boolean('redirect_enabled')->default(true);
    $table->timestamps();

    $table->index(['old_username', 'redirect_enabled']);
});
```

## Model Extensions

### User Model (`app/Models/User.php`)

**Add to fillable:**
```php
protected $fillable = [
    // ... existing fields
    'display_mode',
    'display_mode_changed_at',
];
```

**Add to casts:**
```php
protected $casts = [
    // ... existing casts
    'display_mode_changed_at' => 'datetime',
];
```

**Add relationship:**
```php
/**
 * URL history for redirects
 */
public function urlHistory(): HasMany
{
    return $this->hasMany(UserUrlHistory::class);
}
```

**Add methods:**
```php
/**
 * Check if user has public identity enabled
 */
public function hasPublicIdentity(): bool
{
    return $this->display_mode === 'public';
}

/**
 * Get display name based on identity mode
 */
public function getDisplayName(): string
{
    return $this->hasPublicIdentity()
        ? $this->username
        : $this->anonymized_name;
}

/**
 * Update display mode and track change
 */
public function updateDisplayMode(string $mode): void
{
    if (!in_array($mode, ['anonymous', 'public'])) {
        throw new \InvalidArgumentException('Invalid display mode');
    }

    if ($this->display_mode === $mode) {
        return; // No change needed
    }

    $this->update([
        'display_mode' => $mode,
        'display_mode_changed_at' => now(),
    ]);

    // Trigger event for analytics/notifications
    event(new DisplayModeChanged($this, $this->display_mode, $mode));
}

/**
 * Update username and create redirect
 */
public function updateUsername(string $newUsername): void
{
    $oldUsername = $this->username;

    // Validate username
    if (!$this->isValidUsername($newUsername)) {
        throw new \InvalidArgumentException('Invalid username format');
    }

    // Check uniqueness
    if (static::where('username', $newUsername)->where('id', '!=', $this->id)->exists()) {
        throw new \InvalidArgumentException('Username already taken');
    }

    // Update username
    $this->update(['username' => $newUsername]);

    // Create redirect history
    if ($oldUsername !== $newUsername) {
        UserUrlHistory::create([
            'user_id' => $this->id,
            'old_username' => $oldUsername,
            'new_username' => $newUsername,
            'changed_at' => now(),
            'redirect_enabled' => true,
        ]);
    }
}

/**
 * Validate username format
 */
protected function isValidUsername(string $username): bool
{
    // Alphanumeric, hyphens, underscores, 3-64 characters
    return preg_match('/^[a-zA-Z0-9_-]{3,64}$/', $username);
}

/**
 * Get SEO-optimized meta title
 */
public function getMetaTitle(): string
{
    if (!$this->crystalMetric) {
        return $this->getDisplayName() . "'s Forge | Világműhely";
    }

    $rank = $this->calculateRank($this->crystalMetric->facet_count);
    $colorName = $this->hexToColorName($this->crystalMetric->dominant_colors[0] ?? '#ffffff');

    if ($this->hasPublicIdentity()) {
        return "{$this->username}'s Forge – {$colorName} Crystal {$rank} | Világműhely";
    }

    return "{$this->anonymized_name}'s Forge – {$colorName} Crystal {$rank} | Világműhely";
}

/**
 * Get SEO-optimized meta description
 */
public function getMetaDescription(): string
{
    if (!$this->crystalMetric) {
        return "Explore {$this->getDisplayName()}'s creative forge on Világműhely.";
    }

    $rank = $this->calculateRank($this->crystalMetric->facet_count);
    $level = $this->crystalMetric->facet_count;
    $worksCount = $this->contents()->whereIn('status', [Content::STATUS_PUBLIC, Content::STATUS_MEMBERS_ONLY])->count();
    $aura = round($this->crystalMetric->glow_intensity * 100);

    if ($this->hasPublicIdentity()) {
        return "Explore {$this->username}'s creative forge: Level {$level} {$rank} with {$worksCount} works and {$aura}% aura resonance. Discover unique content and worldbuilding resources.";
    }

    return "Explore {$this->anonymized_name}'s creative forge: Level {$level} {$rank} with {$worksCount} works, {$this->crystalMetric->facet_count} crystal facets, and {$aura}% aura resonance.";
}
```

### UserUrlHistory Model (`app/Models/UserUrlHistory.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserUrlHistory extends Model
{
    protected $fillable = [
        'user_id',
        'old_username',
        'new_username',
        'changed_at',
        'redirect_enabled',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'redirect_enabled' => 'boolean',
    ];

    /**
     * Get the user this history belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Find redirect target for a username
     */
    public static function findRedirectTarget(string $username): ?User
    {
        $history = static::where('old_username', $username)
            ->where('redirect_enabled', true)
            ->orderBy('changed_at', 'desc')
            ->first();

        return $history ? $history->user : null;
    }

    /**
     * Disable redirect
     */
    public function disableRedirect(): void
    {
        $this->update(['redirect_enabled' => false]);
    }
}
```

## Events

### DisplayModeChanged Event (`app/Events/DisplayModeChanged.php`)

```php
<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DisplayModeChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public string $oldMode,
        public string $newMode
    ) {}
}
```

## Middleware

### HandleForgeProfileRedirects (`app/Http/Middleware/HandleForgeProfileRedirects.php`)

```php
<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserUrlHistory;
use Closure;
use Illuminate\Http\Request;

class HandleForgeProfileRedirects
{
    public function handle(Request $request, Closure $next)
    {
        // Only handle Forge profile routes
        if (!$request->route()->named('forge.profile')) {
            return $next($request);
        }

        $username = $request->route('user');

        // Check if user exists with current username
        $user = User::where('username', $username)->first();

        if ($user) {
            return $next($request);
        }

        // Check URL history for redirect
        $redirectTarget = UserUrlHistory::findRedirectTarget($username);

        if ($redirectTarget) {
            return redirect()
                ->route('forge.profile', $redirectTarget->username)
                ->with('info', 'This profile has been moved to a new URL.');
        }

        // No user found and no redirect
        return abort(404, 'Forge profile not found');
    }
}
```

Register in `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ... existing
    'forge.redirect' => \App\Http\Middleware\HandleForgeProfileRedirects::class,
];
```

## Routes Update

Update `routes/web.php`:

```php
// Apply redirect middleware to Forge profile route
Route::get('/forge/{user}', [ForgeController::class, 'show'])
    ->name('forge.profile')
    ->middleware('forge.redirect');
```

## Controller Updates

### ForgeController (`app/Http/Controllers/ForgeController.php`)

Update `show` method to use display name:

```php
public function show(User $user)
{
    // ... existing code

    // Use display name instead of always anonymized
    $displayName = $user->getDisplayName();
    $metaTitle = $user->getMetaTitle();
    $metaDescription = $user->getMetaDescription();

    return view('forge.profile', compact(
        'user',
        'displayName',
        'metaTitle',
        'metaDescription',
        // ... existing variables
    ));
}
```

## Frontend Implementation

### Forge Profile Header Update (`resources/views/forge/profile.blade.php`)

Update meta tags section:

```blade
@section('title', $metaTitle)
@section('meta_description', $metaDescription)
@section('og_title', $metaTitle)
@section('og_description', $metaDescription)
@section('og_image', asset('images/forge-og-' . $user->id . '.jpg'))
```

Update profile header:

```blade
<div class="forge-hero-section">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8">
            {{-- Display name based on identity mode --}}
            <h1 class="text-4xl font-bold text-white mb-2">
                {{ $displayName }}'s Forge
            </h1>

            @if($user->hasPublicIdentity())
                <p class="text-gray-400 text-sm">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                    </svg>
                    Public Profile
                </p>
            @else
                <p class="text-gray-400 text-sm">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    Anonymous Profile
                </p>
            @endif
        </div>
        {{-- Rest of profile --}}
    </div>
</div>
```

### Content Library Author Display

Update content card to use display name. In content list views:

```blade
{{-- In content cards --}}
<div class="content-author text-sm text-gray-400">
    By {{ $content->creator->getDisplayName() }}
    @if($content->creator->hasPublicIdentity())
        <a href="{{ route('forge.profile', $content->creator->username) }}"
           class="text-purple-400 hover:text-purple-300 ml-1">
            View Profile
        </a>
    @endif
</div>
```

### Profile Settings Page

Add identity toggle section in `resources/views/profile/edit.blade.php` or Filament profile page:

```blade
<div class="settings-section border-2 border-yellow-500/50 rounded-lg p-6 bg-yellow-500/10">
    <h2 class="text-xl font-bold text-white mb-2">🔐 Identity & Privacy</h2>
    <p class="text-gray-400 text-sm mb-6">Control how your identity appears on public profiles</p>

    <div class="space-y-6">
        {{-- Display Mode Toggle --}}
        <div class="flex items-start gap-4">
            <div class="flex-1">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="radio"
                        name="display_mode"
                        value="anonymous"
                        {{ !$user->hasPublicIdentity() ? 'checked' : '' }}
                        class="w-4 h-4 text-purple-600"
                    >
                    <div>
                        <div class="font-semibold text-white">
                            🔒 Anonymous Mode (Recommended)
                        </div>
                        <div class="text-sm text-gray-400">
                            Your profile displays as "{{ $user->anonymized_name }}" everywhere on the site
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex items-start gap-4">
            <div class="flex-1">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="radio"
                        name="display_mode"
                        value="public"
                        {{ $user->hasPublicIdentity() ? 'checked' : '' }}
                        class="w-4 h-4 text-purple-600"
                    >
                    <div>
                        <div class="font-semibold text-white">
                            👤 Public Identity
                        </div>
                        <div class="text-sm text-gray-400">
                            Your profile displays as "<strong class="text-white">{{ $user->username }}</strong>" on your Forge page and content contributions
                        </div>
                    </div>
                </label>
            </div>
        </div>

        {{-- Privacy Warning --}}
        <div class="bg-amber-500/20 border border-amber-500/50 rounded-lg p-4 text-sm text-amber-200">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <strong>Privacy Notice:</strong> Enabling "Public Identity" makes your username visible to all visitors.
                    Your real name ({{ $user->name }}) remains <strong>strictly private</strong> and will never be displayed publicly.
                </div>
            </div>
        </div>

        {{-- Current Preview --}}
        <div class="bg-white/10 rounded-lg p-4">
            <div class="text-sm text-gray-400 mb-2">Preview:</div>
            <div class="flex items-center gap-2">
                <div class="text-white font-semibold">
                    {{ $user->getDisplayName() }}
                </div>
                <span class="px-2 py-1 bg-purple-600/30 text-purple-300 rounded text-xs">
                    {{ $user->hasPublicIdentity() ? 'Public' : 'Anonymous' }}
                </span>
            </div>
        </div>
    </div>
</div>
```

## Filament Admin Integration

### User Profile Edit Form

Add to `app/Filament/Admin/Resources/UserResource.php` or profile settings:

```php
Forms\Components\Section::make('Identity & Privacy')
    ->description('Control how this user\'s identity appears on public profiles')
    ->schema([
        Forms\Components\Radio::make('display_mode')
            ->label('Display Mode')
            ->options([
                'anonymous' => 'Anonymous (Creator #ID)',
                'public' => 'Public (Username)',
            ])
            ->default('anonymous')
            ->inline()
            ->helperText('Anonymous mode displays "Creator #ID", Public mode displays the username'),

        Forms\Components\Placeholder::make('privacy_note')
            ->label('')
            ->content('**Privacy:** Real names are never displayed publicly, regardless of display mode. Only the username becomes visible when Public Identity is enabled.')
            ->columnSpanFull(),

        Forms\Components\Placeholder::make('last_changed')
            ->label('Last Changed')
            ->content(fn($record) => $record?->display_mode_changed_at
                ? $record->display_mode_changed_at->diffForHumans()
                : 'Never'),
    ])
    ->collapsible()
    ->collapsed(),
```

### URL History Resource (`app/Filament/Admin/Resources/UserUrlHistoryResource.php`)

```php
<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserUrlHistoryResource\Pages;
use App\Models\UserUrlHistory;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class UserUrlHistoryResource extends Resource
{
    protected static ?string $model = UserUrlHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'URL Redirects';

    protected static ?int $navigationSort = 20;

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.username')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('old_username')
                    ->label('Old Username')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('new_username')
                    ->label('New Username')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('redirect_enabled')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('changed_at')
                    ->label('Changed At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('redirect_enabled')
                    ->label('Active Redirects'),
            ])
            ->actions([
                Tables\Actions\Action::make('test_redirect')
                    ->label('Test')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn($record) => route('forge.profile', $record->old_username))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('disable')
                    ->label('Disable')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->disableRedirect())
                    ->visible(fn($record) => $record->redirect_enabled),
            ])
            ->defaultSort('changed_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserUrlHistories::route('/'),
        ];
    }
}
```

## Profile Update Handler

### ProfileUpdateRequest Extension

Add validation and logic in `app/Http/Requests/ProfileUpdateRequest.php` or create dedicated request:

```php
public function rules(): array
{
    return [
        // ... existing rules
        'display_mode' => ['required', 'in:anonymous,public'],
    ];
}

// After validation, in controller:
public function update(ProfileUpdateRequest $request)
{
    $user = $request->user();

    // Update display mode if changed
    if ($request->filled('display_mode') && $user->display_mode !== $request->display_mode) {
        $user->updateDisplayMode($request->display_mode);

        return back()->with('success', 'Identity display mode updated successfully!');
    }

    // ... existing profile update logic
}
```

## Notification System

### DisplayModeChangedNotification (`app/Notifications/DisplayModeChangedNotification.php`)

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DisplayModeChangedNotification extends Notification
{
    public function __construct(
        public string $oldMode,
        public string $newMode
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mode = $this->newMode === 'public' ? 'Public Identity' : 'Anonymous Mode';
        $displayName = $this->newMode === 'public'
            ? $notifiable->username
            : $notifiable->anonymized_name;

        return (new MailMessage)
            ->subject('Forge Identity Mode Changed')
            ->line("Your Forge profile display mode has been changed to **{$mode}**.")
            ->line("Your public profile now displays as: **{$displayName}**")
            ->line('Your real name remains private and will never be shown publicly.')
            ->action('View Your Forge Profile', route('forge.profile', $notifiable->username))
            ->line('If you did not make this change, please contact support immediately.');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Identity Display Mode Changed',
            'message' => "Your Forge profile is now in {$this->newMode} mode",
            'old_mode' => $this->oldMode,
            'new_mode' => $this->newMode,
        ];
    }
}
```

### Event Listener

Create `app/Listeners/SendDisplayModeChangedNotification.php`:

```php
<?php

namespace App\Listeners;

use App\Events\DisplayModeChanged;
use App\Notifications\DisplayModeChangedNotification;

class SendDisplayModeChangedNotification
{
    public function handle(DisplayModeChanged $event): void
    {
        $event->user->notify(
            new DisplayModeChangedNotification($event->oldMode, $event->newMode)
        );
    }
}
```

Register in `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    DisplayModeChanged::class => [
        SendDisplayModeChangedNotification::class,
    ],
];
```

## SEO & Social Sharing

### Dynamic Open Graph Images

Update OG image generation to include username when public:

```php
// In ForgeController or dedicated service
public function generateOGImage(User $user)
{
    // Use username in OG image if public identity
    $displayText = $user->hasPublicIdentity()
        ? $user->username
        : $user->anonymized_name;

    // Generate image with display name
    // ... image generation logic
}
```

### Structured Data (JSON-LD)

Update structured data in Forge profile:

```blade
@push('head')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "ProfilePage",
    "name": "{{ $displayName }}'s Forge",
    "description": "{{ $metaDescription }}",
    @if($user->hasPublicIdentity())
    "author": {
        "@type": "Person",
        "name": "{{ $user->username }}",
        "url": "{{ route('forge.profile', $user->username) }}"
    },
    @endif
    "url": "{{ route('forge.profile', $user->username) }}"
}
</script>
@endpush
```

## Testing Requirements

### Unit Tests

**UserIdentityTest** (`tests/Unit/UserIdentityTest.php`):
```php
test('user can have public identity mode', function() {
    $user = User::factory()->create(['display_mode' => 'public']);

    expect($user->hasPublicIdentity())->toBeTrue();
});

test('user defaults to anonymous mode', function() {
    $user = User::factory()->create();

    expect($user->hasPublicIdentity())->toBeFalse();
});

test('user display name returns username when public', function() {
    $user = User::factory()->create([
        'display_mode' => 'public',
        'username' => 'test-creator',
    ]);

    expect($user->getDisplayName())->toBe('test-creator');
});

test('user display name returns anonymized when private', function() {
    $user = User::factory()->create([
        'display_mode' => 'anonymous',
        'id' => 42,
    ]);

    expect($user->getDisplayName())->toBe('Creator #42');
});

test('user can update display mode', function() {
    $user = User::factory()->create(['display_mode' => 'anonymous']);

    $user->updateDisplayMode('public');

    expect($user->fresh()->display_mode)->toBe('public')
        ->and($user->fresh()->display_mode_changed_at)->not->toBeNull();
});

test('user cannot set invalid display mode', function() {
    $user = User::factory()->create();

    $user->updateDisplayMode('invalid');
})->throws(\InvalidArgumentException::class);

test('user meta title includes username when public', function() {
    $user = User::factory()->create([
        'display_mode' => 'public',
        'username' => 'amazing-creator',
    ]);

    $metaTitle = $user->getMetaTitle();

    expect($metaTitle)->toContain('amazing-creator');
});

test('user meta description includes username when public', function() {
    $user = User::factory()
        ->has(UserCrystalMetric::factory())
        ->create([
            'display_mode' => 'public',
            'username' => 'test-user',
        ]);

    $metaDescription = $user->getMetaDescription();

    expect($metaDescription)->toContain('test-user');
});
```

**UserUrlHistoryTest** (`tests/Unit/UserUrlHistoryTest.php`):
```php
test('url history tracks username changes', function() {
    $user = User::factory()->create(['username' => 'old-name']);

    $user->updateUsername('new-name');

    expect(UserUrlHistory::where('user_id', $user->id)->count())->toBe(1)
        ->and(UserUrlHistory::first()->old_username)->toBe('old-name')
        ->and(UserUrlHistory::first()->new_username)->toBe('new-name');
});

test('url history can find redirect target', function() {
    $user = User::factory()->create(['username' => 'old-name']);
    $user->updateUsername('new-name');

    $target = UserUrlHistory::findRedirectTarget('old-name');

    expect($target->id)->toBe($user->id)
        ->and($target->username)->toBe('new-name');
});

test('disabled redirects are not found', function() {
    $user = User::factory()->create(['username' => 'old-name']);
    $user->updateUsername('new-name');

    $history = UserUrlHistory::first();
    $history->disableRedirect();

    $target = UserUrlHistory::findRedirectTarget('old-name');

    expect($target)->toBeNull();
});
```

### Feature Tests

**ForgeIdentityDisplayTest** (`tests/Feature/ForgeIdentityDisplayTest.php`):
```php
test('forge profile displays username when public mode', function() {
    $user = User::factory()->create([
        'display_mode' => 'public',
        'username' => 'amazing-creator',
    ]);

    $response = $this->get(route('forge.profile', $user->username));

    $response->assertOk()
        ->assertSee('amazing-creator\'s Forge')
        ->assertSee('Public Profile');
});

test('forge profile displays anonymized name when anonymous mode', function() {
    $user = User::factory()->create([
        'display_mode' => 'anonymous',
        'id' => 99,
    ]);

    $response = $this->get(route('forge.profile', $user->username));

    $response->assertOk()
        ->assertSee('Creator #99\'s Forge')
        ->assertSee('Anonymous Profile');
});

test('content library displays username when public mode', function() {
    $user = User::factory()->create([
        'display_mode' => 'public',
        'username' => 'public-creator',
    ]);

    $content = Content::factory()->create(['creator_id' => $user->id]);

    $response = $this->get(route('library.index'));

    $response->assertOk()
        ->assertSee('public-creator');
});

test('old username redirects to new username', function() {
    $user = User::factory()->create(['username' => 'old-name']);
    $user->updateUsername('new-name');

    $response = $this->get(route('forge.profile', 'old-name'));

    $response->assertRedirect(route('forge.profile', 'new-name'))
        ->assertSessionHas('info');
});

test('non-existent username returns 404', function() {
    $response = $this->get(route('forge.profile', 'non-existent'));

    $response->assertNotFound();
});
```

**ProfileSettingsTest** (`tests/Feature/ProfileSettingsTest.php`):
```php
test('user can update display mode to public', function() {
    $user = User::factory()->create(['display_mode' => 'anonymous']);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'display_mode' => 'public',
        ]);

    expect($user->fresh()->display_mode)->toBe('public');
});

test('user receives notification when display mode changes', function() {
    Notification::fake();

    $user = User::factory()->create(['display_mode' => 'anonymous']);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'display_mode' => 'public',
        ]);

    Notification::assertSentTo($user, DisplayModeChangedNotification::class);
});
```

## Performance Considerations

### Caching
- Cache user display name (5 min TTL)
- Cache meta tags per user (15 min TTL)
- Invalidate caches on display mode change

### Database Indexes
- Index `users.display_mode` for filtering
- Index `user_url_history.old_username` for redirect lookups
- Index `user_url_history.redirect_enabled` for active redirects

## Security & Privacy

### Privacy Protections
- Real name (`users.name`) is NEVER displayed publicly
- Only username is shown in public mode
- Email addresses remain private
- Profile URLs cannot expose sensitive information

### Validation
- Username format: alphanumeric, hyphens, underscores (3-64 chars)
- Username uniqueness checked
- Display mode restricted to valid values
- CSRF protection on all forms

### Audit Trail
- Track display mode changes with timestamps
- Log username changes in URL history
- Notification sent on identity changes

## Configuration

Add to `config/forge.php`:

```php
return [
    // Identity settings
    'identity' => [
        'default_display_mode' => 'anonymous',
        'allow_mode_changes' => true,
        'notify_on_change' => true,
    ],

    // Username settings
    'username' => [
        'min_length' => 3,
        'max_length' => 64,
        'pattern' => '/^[a-zA-Z0-9_-]+$/',
        'reserved' => ['admin', 'administrator', 'moderator', 'support', 'system'],
    ],

    // URL redirect settings
    'redirects' => [
        'enabled' => true,
        'keep_history_days' => 365, // Keep redirect history for 1 year
        'max_redirects_per_user' => 10, // Limit redirect chains
    ],
];
```

## Implementation Order

1. Create migrations (display_mode, user_url_history)
2. Create UserUrlHistory model
3. Extend User model with identity methods
4. Create DisplayModeChanged event
5. Create redirect middleware
6. Update routes with middleware
7. Update ForgeController for SEO
8. Update Forge profile template
9. Update content library displays
10. Create profile settings UI
11. Add Filament admin fields
12. Create URL history resource
13. Create notification system
14. Write unit and feature tests
15. Test redirect functionality
16. Deploy and monitor

## Migration Checklist

- [ ] Run migrations
- [ ] Test display mode toggle in profile settings
- [ ] Verify username display on Forge profiles
- [ ] Verify anonymized display in anonymous mode
- [ ] Test username change creates redirect
- [ ] Test old URL redirects to new username
- [ ] Verify SEO meta tags update correctly
- [ ] Test content library shows correct identity
- [ ] Test privacy warning displays
- [ ] Verify notifications sent on mode change
- [ ] Test admin URL history resource
- [ ] Verify redirect middleware works
- [ ] Test 404 for non-existent profiles
- [ ] Check social sharing previews
- [ ] Verify real name never exposed

## Future Enhancements

- **Verified Badges**: Add verification system for public identities
- **Social Links**: Allow adding social media links in public mode
- **Custom Slugs**: Allow custom profile URLs beyond username
- **Analytics**: Track conversion from anonymous to public mode
- **Profile Customization**: More customization options for public profiles
- **Username Change Limits**: Rate limit username changes
- **Username Marketplace**: Allow users to claim premium usernames
