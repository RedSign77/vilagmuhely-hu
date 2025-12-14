<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webtechsolutions\ContentEngine\Models\Content;

class ContentReview extends Model
{
    use SoftDeletes;

    /**
     * Review status constants.
     */
    const STATUS_PENDING = 'pending';

    const STATUS_APPROVED = 'approved';

    const STATUS_REJECTED = 'rejected';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'content_id',
        'user_id',
        'title',
        'review_text',
        'status',
        'moderation_notes',
        'moderated_by',
        'moderated_at',
        'helpful_votes',
        'is_edited',
        'edited_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_edited' => 'boolean',
        'helpful_votes' => 'integer',
        'moderated_at' => 'datetime',
        'edited_at' => 'datetime',
    ];

    /**
     * Get the content being reviewed.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Get the user who wrote the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the moderator who moderated this review.
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * Get the votes for this review.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(ContentReviewVote::class, 'review_id');
    }

    /**
     * Scope to get only approved reviews.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope to get only pending reviews.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get most helpful reviews.
     */
    public function scopeMostHelpful(Builder $query, int $limit = 10): Builder
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->orderByDesc('helpful_votes')
            ->limit($limit);
    }

    /**
     * Scope to get recent reviews.
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days))
            ->orderByDesc('created_at');
    }

    /**
     * Mark the review as edited.
     */
    public function markAsEdited(): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }

    /**
     * Approve the review.
     */
    public function approve(?int $moderatorId = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'moderated_by' => $moderatorId ?? auth()->id(),
            'moderated_at' => now(),
        ]);
    }

    /**
     * Reject the review.
     */
    public function reject(?int $moderatorId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'moderation_notes' => $notes,
            'moderated_by' => $moderatorId ?? auth()->id(),
            'moderated_at' => now(),
        ]);
    }

    /**
     * Increment the helpful votes count.
     */
    public function incrementHelpfulVotes(): void
    {
        $this->increment('helpful_votes');
    }

    /**
     * Decrement the helpful votes count.
     */
    public function decrementHelpfulVotes(): void
    {
        $this->decrement('helpful_votes');
    }
}
