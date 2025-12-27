<?php

return [
    // Default expedition duration in days
    'default_duration_days' => 30,

    // Default max participants (null = unlimited)
    'default_max_participants' => null,

    // Minimum word count for posts
    'min_word_count' => 500,

    // Default crystal multiplier
    'default_crystal_multiplier' => 2.0,

    // Default reward bonuses
    'default_engagement_bonus' => 100,
    'default_interaction_bonus' => 50,

    // Visual effect durations (days)
    'effect_durations' => [
        'expedition_winner_aura' => 30,
        'crystal_surge' => 7,
        'spectral_shimmer' => 14,
    ],

    // Progress notification thresholds (%)
    'progress_notification_thresholds' => [25, 50, 75],

    // Reminder notification timing
    'ending_soon_hours' => 24,

    // Pagination
    'per_page' => 12,

    // Cache TTL (minutes)
    'cache_ttl' => 60,
];
