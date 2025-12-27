<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Expedition extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
        'starts_at',
        'ends_at',
        'requirements',
        'rewards',
        'max_participants',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'requirements' => 'array',
        'rewards' => 'array',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expedition) {
            if (empty($expedition->slug)) {
                $expedition->slug = static::generateUniqueSlug($expedition->title);
            }
        });
    }

    /**
     * Generate unique slug from title
     */
    protected static function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get enrollments for this expedition
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(ExpeditionEnrollment::class);
    }

    /**
     * Get participants (users) for this expedition
     */
    public function participants(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            ExpeditionEnrollment::class,
            'expedition_id',
            'id',
            'id',
            'user_id'
        );
    }

    /**
     * Get qualifying posts for this expedition
     */
    public function qualifyingPosts(): HasMany
    {
        return $this->hasMany(ExpeditionQualifyingPost::class);
    }

    /**
     * Scope: Active expeditions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    /**
     * Scope: Upcoming expeditions
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'active')
            ->where('starts_at', '>', now());
    }

    /**
     * Scope: Completed expeditions
     */
    public function scopeCompleted($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'completed')
                ->orWhere('ends_at', '<', now());
        });
    }

    /**
     * Scope: Enrollable expeditions
     */
    public function scopeEnrollable($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('max_participants')
                    ->orWhereRaw('(SELECT COUNT(*) FROM expedition_enrollments WHERE expedition_id = expeditions.id) < max_participants');
            });
    }

    /**
     * Check if expedition is currently active
     */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && now()->between($this->starts_at, $this->ends_at);
    }

    /**
     * Check if users can enroll
     */
    public function isEnrollable(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->max_participants === null) {
            return true;
        }

        return $this->getParticipantCount() < $this->max_participants;
    }

    /**
     * Check if expedition has ended
     */
    public function hasEnded(): bool
    {
        return $this->ends_at < now();
    }

    /**
     * Get remaining enrollment slots
     */
    public function getRemainingSlots(): ?int
    {
        if ($this->max_participants === null) {
            return null;
        }

        return max(0, $this->max_participants - $this->getParticipantCount());
    }

    /**
     * Get participant count
     */
    public function getParticipantCount(): int
    {
        return $this->enrollments()->count();
    }

    /**
     * Get completion rate
     */
    public function getCompletionRate(): float
    {
        $total = $this->getParticipantCount();

        if ($total === 0) {
            return 0.0;
        }

        $completed = $this->enrollments()->whereNotNull('completed_at')->count();

        return ($completed / $total) * 100;
    }

    /**
     * Get route key name for route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
