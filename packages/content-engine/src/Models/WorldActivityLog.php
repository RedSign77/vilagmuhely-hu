<?php

namespace Webtechsolutions\ContentEngine\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldActivityLog extends Model
{
    protected $table = 'world_activity_log';

    protected $fillable = [
        'user_id',
        'activity_type',
        'structure_id',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    // Activity type constants
    public const TYPE_STRUCTURE_PLACED = 'structure_placed';

    public const TYPE_STRUCTURE_UPGRADED = 'structure_upgraded';

    public const TYPE_RESOURCE_EARNED = 'resource_earned';

    public const TYPE_STRUCTURE_REMOVED = 'structure_removed';

    /**
     * Get the user who performed this activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the structure associated with this activity
     */
    public function structure(): BelongsTo
    {
        return $this->belongsTo(WorldStructure::class, 'structure_id');
    }

    /**
     * Log an activity
     */
    public static function log(int $userId, string $activityType, ?int $structureId = null, ?array $details = null): self
    {
        return self::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'structure_id' => $structureId,
            'details' => $details,
        ]);
    }

    /**
     * Scope to get activities of a specific type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope to get recent activities
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}
