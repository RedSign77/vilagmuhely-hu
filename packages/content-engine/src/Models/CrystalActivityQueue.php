<?php

namespace Webtechsolutions\ContentEngine\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrystalActivityQueue extends Model
{
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    protected $table = 'crystal_activity_queue';

    protected $fillable = [
        'user_id',
        'activity_type',
        'metadata',
        'processed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Activity type constants
     */
    public const TYPE_CONTENT_PUBLISHED = 'content_published';
    public const TYPE_CONTENT_VIEWED = 'content_viewed';
    public const TYPE_CONTENT_DOWNLOADED = 'content_downloaded';
    public const TYPE_CONTENT_RATED = 'content_rated';
    public const TYPE_ACHIEVEMENT_UNLOCKED = 'achievement_unlocked';

    /**
     * Get the user this activity belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Add a new activity to the queue
     */
    public static function addActivity(int $userId, string $activityType, ?array $metadata = null): self
    {
        return static::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get unprocessed activities
     */
    public function scopeUnprocessed($query)
    {
        return $query->whereNull('processed_at');
    }

    /**
     * Get processed activities
     */
    public function scopeProcessed($query)
    {
        return $query->whereNotNull('processed_at');
    }

    /**
     * Get activities for a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get activities of a specific type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Mark activities as processed
     */
    public function markProcessed(): void
    {
        $this->update(['processed_at' => now()]);
    }

    /**
     * Get distinct user IDs with unprocessed activities
     */
    public static function getUnprocessedUserIds(): array
    {
        return static::unprocessed()
            ->distinct('user_id')
            ->pluck('user_id')
            ->toArray();
    }

    /**
     * Cleanup old processed activities (older than 30 days)
     */
    public static function cleanupOld(int $days = 30): int
    {
        return static::processed()
            ->where('processed_at', '<=', now()->subDays($days))
            ->delete();
    }
}
