<?php

namespace App\Services;

use App\Models\CrystalActivityQueue;
use App\Models\Expedition;
use App\Models\ExpeditionEnrollment;
use App\Models\User;
use App\Models\UserExpeditionEffect;
use App\Notifications\ExpeditionCompletedNotification;

class ExpeditionRewardService
{
    /**
     * Grant all rewards for completed enrollment
     */
    public function grantRewards(ExpeditionEnrollment $enrollment): void
    {
        $user = $enrollment->user;
        $expedition = $enrollment->expedition;
        $rewards = $expedition->rewards;

        // Grant crystal multiplier and bonuses
        $this->applyCrystalBonuses($user, $expedition, $rewards);

        // Activate visual effect
        if (isset($rewards['visual_effect'])) {
            $this->activateVisualEffect($user, $expedition, $rewards);
        }

        // Send completion notification
        $this->sendCompletionNotification($user, $expedition);

        // Mark reward as claimed
        $enrollment->claimReward();
    }

    /**
     * Apply crystal multiplier and bonuses
     */
    protected function applyCrystalBonuses(User $user, Expedition $expedition, array $rewards): void
    {
        CrystalActivityQueue::create([
            'user_id' => $user->id,
            'activity_type' => CrystalActivityQueue::TYPE_EXPEDITION_COMPLETED,
            'metadata' => [
                'expedition_id' => $expedition->id,
                'expedition_title' => $expedition->title,
                'crystal_multiplier' => $rewards['crystal_multiplier'] ?? 2.0,
                'engagement_bonus' => $rewards['engagement_bonus'] ?? 100,
                'interaction_bonus' => $rewards['interaction_bonus'] ?? 50,
            ],
        ]);
    }

    /**
     * Activate visual effect for user
     */
    protected function activateVisualEffect(User $user, Expedition $expedition, array $rewards): void
    {
        $effectType = $rewards['visual_effect'];
        $durationDays = $rewards['effect_duration_days'] ?? config('expeditions.effect_durations.'.$effectType, 30);

        UserExpeditionEffect::create([
            'user_id' => $user->id,
            'expedition_id' => $expedition->id,
            'effect_type' => $effectType,
            'activated_at' => now(),
            'expires_at' => now()->addDays($durationDays),
            'is_active' => true,
        ]);

        // Queue effect activation activity
        CrystalActivityQueue::create([
            'user_id' => $user->id,
            'activity_type' => CrystalActivityQueue::TYPE_EXPEDITION_EFFECT_ACTIVATED,
            'metadata' => [
                'expedition_id' => $expedition->id,
                'effect_type' => $effectType,
                'duration_days' => $durationDays,
            ],
        ]);
    }

    /**
     * Send completion notification
     */
    protected function sendCompletionNotification(User $user, Expedition $expedition): void
    {
        $user->notify(new ExpeditionCompletedNotification($expedition));
    }
}
