<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentReviewVote extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'review_id',
        'user_id',
        'is_helpful',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_helpful' => 'boolean',
    ];

    /**
     * Get the review that was voted on.
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(ContentReview::class);
    }

    /**
     * Get the user who voted.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
