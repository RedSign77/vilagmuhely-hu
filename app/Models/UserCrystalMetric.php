<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCrystalMetric extends Model
{
    protected $fillable = [
        'user_id',
        'total_content_count',
        'diversity_index',
        'interaction_score',
        'engagement_score',
        'facet_count',
        'glow_intensity',
        'purity_level',
        'dominant_colors',
        'cached_geometry',
        'last_calculated_at',
    ];

    protected $casts = [
        'total_content_count' => 'integer',
        'diversity_index' => 'float',
        'interaction_score' => 'float',
        'engagement_score' => 'float',
        'facet_count' => 'integer',
        'glow_intensity' => 'float',
        'purity_level' => 'float',
        'dominant_colors' => 'array',
        'cached_geometry' => 'array',
        'last_calculated_at' => 'datetime',
    ];

    /**
     * Get the user this metric belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if metrics need updating
     * (based on last calculation time)
     */
    public function needsUpdate(int $thresholdMinutes = 30): bool
    {
        if (!$this->last_calculated_at) {
            return true;
        }

        return $this->last_calculated_at->diffInMinutes(now()) >= $thresholdMinutes;
    }

    /**
     * Get crystal data formatted for API response
     */
    public function getCrystalData(): array
    {
        return [
            'metrics' => [
                'total_content' => $this->total_content_count,
                'diversity' => round($this->diversity_index, 3),
                'interaction_score' => round($this->interaction_score, 2),
                'engagement_score' => round($this->engagement_score, 2),
            ],
            'crystal' => [
                'facets' => $this->facet_count,
                'glow_intensity' => round($this->glow_intensity, 2),
                'purity' => round($this->purity_level, 2),
                'colors' => $this->dominant_colors ?? [],
            ],
            'geometry' => $this->cached_geometry ?? null,
            'last_updated' => $this->last_calculated_at?->toIso8601String(),
        ];
    }

    /**
     * Scope to get metrics that need recalculation
     */
    public function scopeNeedsRecalculation($query, int $thresholdMinutes = 30)
    {
        return $query->where(function ($q) use ($thresholdMinutes) {
            $q->whereNull('last_calculated_at')
              ->orWhere('last_calculated_at', '<=', now()->subMinutes($thresholdMinutes));
        });
    }

    /**
     * Scope to get recently updated metrics
     */
    public function scopeRecentlyUpdated($query, int $minutes = 60)
    {
        return $query->where('last_calculated_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope to get top performers by interaction score
     */
    public function scopeTopInteraction($query, int $limit = 10)
    {
        return $query->orderByDesc('interaction_score')->limit($limit);
    }

    /**
     * Scope to get most diverse creators
     */
    public function scopeTopDiversity($query, int $limit = 10)
    {
        return $query->orderByDesc('diversity_index')->limit($limit);
    }
}
