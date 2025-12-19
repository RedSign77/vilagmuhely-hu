<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\UserCrystalMetric;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Webtechsolutions\ContentEngine\Models\Content;
use Webtechsolutions\ContentEngine\Models\UserWorldResource;
use Webtechsolutions\UserManager\Models\Role;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
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
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * Get the URL to the user's avatar for Filament.
     *
     * @return string|null
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }
}
