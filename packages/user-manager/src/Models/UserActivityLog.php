<?php

namespace Webtechsolutions\UserManager\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivityLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'user_activity_logs';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'activity_type',
        'ip_address',
        'user_agent',
        'properties',
        'description',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Activity type constants
     */
    public const TYPE_LOGIN = 'login';
    public const TYPE_LOGOUT = 'logout';
    public const TYPE_FAILED_LOGIN = 'failed_login';
    public const TYPE_PROFILE_CHANGE = 'profile_change';
    public const TYPE_PASSWORD_CHANGE = 'password_change';
    public const TYPE_ROLE_CHANGE = 'role_change';

    /**
     * Get all activity types
     */
    public static function getActivityTypes(): array
    {
        return [
            self::TYPE_LOGIN => 'Login',
            self::TYPE_LOGOUT => 'Logout',
            self::TYPE_FAILED_LOGIN => 'Failed Login',
            self::TYPE_PROFILE_CHANGE => 'Profile Change',
            self::TYPE_PASSWORD_CHANGE => 'Password Change',
            self::TYPE_ROLE_CHANGE => 'Role Change',
        ];
    }

    /**
     * Get the user that performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include specific activity type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope a query to only include activities from specific IP.
     */
    public function scopeFromIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Scope a query to only include activities older than given days.
     */
    public function scopeOlderThanDays($query, int $days)
    {
        return $query->where('created_at', '<=', now()->subDays($days));
    }

    /**
     * Delete activity logs older than specified days.
     */
    public static function deleteOlderThan(int $days): int
    {
        return static::olderThanDays($days)->delete();
    }

    /**
     * Log a user activity
     */
    public static function log(
        ?int $userId,
        string $activityType,
        ?string $description = null,
        ?array $properties = null
    ): self {
        return static::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    /**
     * Get a human-readable activity type label
     */
    public function getActivityTypeLabelAttribute(): string
    {
        return self::getActivityTypes()[$this->activity_type] ?? $this->activity_type;
    }

    /**
     * Get browser name from user agent
     */
    public function getBrowserAttribute(): ?string
    {
        if (!$this->user_agent) {
            return null;
        }

        if (str_contains($this->user_agent, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($this->user_agent, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($this->user_agent, 'Safari')) {
            return 'Safari';
        } elseif (str_contains($this->user_agent, 'Edge')) {
            return 'Edge';
        } elseif (str_contains($this->user_agent, 'Opera')) {
            return 'Opera';
        }

        return 'Unknown';
    }

    /**
     * Get platform from user agent
     */
    public function getPlatformAttribute(): ?string
    {
        if (!$this->user_agent) {
            return null;
        }

        if (str_contains($this->user_agent, 'Windows')) {
            return 'Windows';
        } elseif (str_contains($this->user_agent, 'Mac')) {
            return 'macOS';
        } elseif (str_contains($this->user_agent, 'Linux')) {
            return 'Linux';
        } elseif (str_contains($this->user_agent, 'Android')) {
            return 'Android';
        } elseif (str_contains($this->user_agent, 'iOS')) {
            return 'iOS';
        }

        return 'Unknown';
    }
}
