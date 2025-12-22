<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Webtechsolutions\ContentEngine\Models\Content;
use Webtechsolutions\ContentEngine\Models\UserWorldResource;
use Webtechsolutions\UserManager\Models\Role;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail, Sitemapable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'email_verified_at',
        'avatar',
        'mobile',
        'city',
        'address',
        'social_media_links',
        'about',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'social_media_links' => 'array',
        ];
    }

    /**
     * Get the roles for the user
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get contents created by this user
     */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class, 'creator_id');
    }

    /**
     * Get user's world resources
     */
    public function worldResources(): HasOne
    {
        return $this->hasOne(UserWorldResource::class);
    }

    /**
     * Get user's crystal metrics
     */
    public function crystalMetric(): HasOne
    {
        return $this->hasOne(UserCrystalMetric::class);
    }

    /**
     * Get content downloads by this user
     */
    public function downloads(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'content_user_downloads')
            ->withPivot(['downloaded_at', 'ip_address'])
            ->using(ContentDownload::class);
    }

    /**
     * Get ratings given by this user
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(ContentRating::class);
    }

    /**
     * Get reviews written by this user
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ContentReview::class);
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    /**
     * Check if user is a supervisor
     */
    public function isSupervisor(): bool
    {
        return $this->roles()->where('is_supervisor', true)->exists();
    }

    /**
     * Check if user is a creator
     */
    public function isCreator(): bool
    {
        return $this->hasRole('creators');
    }

    /**
     * Determine if the user can access the Filament panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasVerifiedEmail();
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * Get the URL to the user's avatar for Filament.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar ? asset('storage/'.$this->avatar) : null;
    }

    /**
     * Convert the user to a sitemap tag.
     */
    public function toSitemapTag(): Url | string | array
    {
        return Url::create(route('crystals.show', $this))
            ->setLastModificationDate($this->updated_at)
            ->setPriority(0.8)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);
    }

    /**
     * Get the anonymized display name for public pages.
     */
    public function getAnonymizedNameAttribute(): string
    {
        return 'Creator #'.$this->id;
    }

    /**
     * Get the anonymized avatar URL for public pages.
     */
    public function getAnonymizedAvatarAttribute(): ?string
    {
        return null;
    }

    /**
     * Get the route key name for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'username';
    }

    /**
     * Get RPG-style stats based on crystal metrics.
     */
    public function getRpgStatsAttribute(): array
    {
        $metric = $this->crystalMetric;

        if (! $metric) {
            return [
                'rank' => 'Novice',
                'level' => 1,
                'aura' => 0,
                'essence' => 0,
            ];
        }

        // Rank/Level based on facet_count (Complexity)
        $level = min(50, max(1, $metric->facet_count));
        $rank = $this->calculateRank($level);

        // Aura based on glow_intensity (Brightness) - 0-100 scale
        $aura = round($metric->glow_intensity * 100);

        // Essence based on purity_level (Clarity) - 0-100 scale
        $essence = round($metric->purity_level * 100);

        return [
            'rank' => $rank,
            'level' => $level,
            'aura' => $aura,
            'essence' => $essence,
        ];
    }

    /**
     * Calculate rank title based on level.
     */
    private function calculateRank(int $level): string
    {
        return match (true) {
            $level >= 45 => 'Crystal Master',
            $level >= 35 => 'Artisan',
            $level >= 25 => 'Craftsperson',
            $level >= 15 => 'Apprentice',
            $level >= 8 => 'Journeyman',
            default => 'Novice',
        };
    }

    /**
     * Get color name for meta description from dominant color.
     */
    public function getCrystalColorNameAttribute(): string
    {
        $metric = $this->crystalMetric;

        if (! $metric || empty($metric->dominant_colors)) {
            return 'Gray';
        }

        $hex = $metric->dominant_colors[0];

        return $this->hexToColorName($hex);
    }

    /**
     * Convert hex color to simple color name.
     */
    private function hexToColorName(string $hex): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Simple color name mapping based on dominant channel
        if ($r > $g && $r > $b) {
            return 'Red';
        }
        if ($g > $r && $g > $b) {
            return 'Green';
        }
        if ($b > $r && $b > $g) {
            return 'Blue';
        }
        if ($r > 200 && $g > 200 && $b < 100) {
            return 'Yellow';
        }
        if ($r > 200 && $b > 200 && $g < 150) {
            return 'Purple';
        }
        if ($g > 200 && $b > 200 && $r < 150) {
            return 'Cyan';
        }

        return 'Multi-colored';
    }

    /**
     * Get recent activity feed items.
     */
    public function getRecentActivities(int $limit = 20)
    {
        return CrystalActivityQueue::where('user_id', $this->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
