<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webtechsolutions\ContentEngine\Models\Content;

class ContentDownload extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'content_user_downloads';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'content_id',
        'user_id',
        'downloaded_at',
        'ip_address',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    /**
     * Get the content that was downloaded.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Get the user who downloaded the content.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a user has downloaded specific content.
     */
    public static function hasUserDownloaded(int $contentId, int $userId): bool
    {
        return static::where('content_id', $contentId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Record a new download.
     */
    public static function recordDownload(int $contentId, int $userId, ?string $ipAddress = null): ?self
    {
        // Only record if user hasn't downloaded this content before
        if (static::hasUserDownloaded($contentId, $userId)) {
            return null;
        }

        return static::create([
            'content_id' => $contentId,
            'user_id' => $userId,
            'downloaded_at' => now(),
            'ip_address' => $ipAddress ?? request()->ip(),
        ]);
    }
}
