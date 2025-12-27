<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpeditionEnrollment extends Model
{
    protected $fillable = [
        'expedition_id',
        'user_id',
        'enrolled_at',
        'completed_at',
        'progress',
        'reward_claimed',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress' => 'array',
        'reward_claimed' => 'boolean',
    ];

    /**
     * Get the expedition this enrollment belongs to
     */
    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * Get the user this enrollment belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get qualifying posts for this enrollment
     */
    public function qualifyingPosts(): HasMany
    {
        return $this->hasMany(ExpeditionQualifyingPost::class, 'enrollment_id');
    }

    /**
     * Check if enrollment is completed
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Get progress or default structure
     */
    public function getProgress(): array
    {
        return $this->progress ?? [
            'posts_created' => 0,
            'total_required' => $this->expedition->requirements['required_count'] ?? 3,
            'qualifying_post_ids' => [],
            'last_checked_at' => null,
        ];
    }

    /**
     * Update progress data
     */
    public function updateProgress(array $data): void
    {
        $currentProgress = $this->getProgress();
        $this->update(['progress' => array_merge($currentProgress, $data)]);
    }

    /**
     * Check if requirements are met
     */
    public function checkCompletion(): bool
    {
        $requirements = $this->expedition->requirements;
        $requiredCount = $requirements['required_count'] ?? 3;

        $qualifyingCount = $this->qualifyingPosts()->count();

        return $qualifyingCount >= $requiredCount;
    }

    /**
     * Claim reward
     */
    public function claimReward(): void
    {
        if (!$this->isCompleted()) {
            throw new \Exception('Cannot claim reward for incomplete enrollment');
        }

        if ($this->reward_claimed) {
            throw new \Exception('Reward already claimed');
        }

        $this->update(['reward_claimed' => true]);
    }
}
