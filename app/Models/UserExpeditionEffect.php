<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserExpeditionEffect extends Model
{
    protected $fillable = [
        'user_id',
        'expedition_id',
        'effect_type',
        'activated_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user this effect belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the expedition this effect came from
     */
    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * Scope: Active effects
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('expires_at', '>', now());
    }

    /**
     * Scope: Expired effects
     */
    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('expires_at', '<=', now())
                ->orWhere('is_active', false);
        });
    }

    /**
     * Check if effect is currently active
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->expires_at > now();
    }

    /**
     * Deactivate the effect
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Extend effect duration
     */
    public function extend(int $days): void
    {
        $this->update([
            'expires_at' => $this->expires_at->addDays($days),
        ]);
    }
}
