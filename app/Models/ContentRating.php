<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webtechsolutions\ContentEngine\Models\Content;

class ContentRating extends Model
{
    protected $fillable = [
        'content_id',
        'user_id',
        'rating',
        'critique_text',
        'is_helpful',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_helpful' => 'boolean',
    ];

    /**
     * Get the content being rated
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Get the user who created the rating
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark this rating as helpful
     */
    public function markAsHelpful(): void
    {
        $this->update(['is_helpful' => true]);
    }

    /**
     * Check if rating is high quality (4-5 stars)
     */
    public function isHighQuality(): bool
    {
        return $this->rating >= 4;
    }

    /**
     * Scope to get only helpful ratings
     */
    public function scopeHelpful($query)
    {
        return $query->where('is_helpful', true);
    }

    /**
     * Scope to get recent ratings
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get high ratings (4-5 stars)
     */
    public function scopeHighRating($query)
    {
        return $query->where('rating', '>=', 4);
    }

    /**
     * Scope to get ratings with critiques
     */
    public function scopeWithCritique($query)
    {
        return $query->whereNotNull('critique_text');
    }
}
