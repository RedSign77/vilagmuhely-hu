<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Webtechsolutions\UserManager\Models\Role;
use Webtechsolutions\ContentEngine\Models\Content;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
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
     * Determine if the user can access the Filament panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasVerifiedEmail();
    }
}
