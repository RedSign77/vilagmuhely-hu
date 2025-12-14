<?php

namespace App\Observers;

use App\Models\User;
use App\Services\CrystalCalculatorService;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    protected CrystalCalculatorService $crystalCalculator;

    public function __construct(CrystalCalculatorService $crystalCalculator)
    {
        $this->crystalCalculator = $crystalCalculator;
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Check if any profile fields were changed
        $profileFields = [
            'avatar',
            'about',
            'mobile',
            'city',
            'address',
            'social_media_links',
        ];

        $profileChanged = false;
        foreach ($profileFields as $field) {
            if ($user->wasChanged($field)) {
                $profileChanged = true;
                break;
            }
        }

        // If profile was updated, recalculate crystal metrics
        if ($profileChanged) {
            // Use DB::afterCommit to defer the recalculation
            \DB::afterCommit(function () use ($user) {
                try {
                    $this->crystalCalculator->recalculateMetrics($user);
                } catch (\Exception $e) {
                    // Log error but don't fail the user update
                    Log::error('Failed to recalculate crystal metrics for user ' . $user->id . ': ' . $e->getMessage());
                }
            });
        }
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // When a new user is created, initialize their crystal metrics
        // This will apply the initial modifier based on their profile completeness
        \DB::afterCommit(function () use ($user) {
            try {
                $this->crystalCalculator->recalculateMetrics($user);
            } catch (\Exception $e) {
                Log::error('Failed to initialize crystal metrics for new user ' . $user->id . ': ' . $e->getMessage());
            }
        });
    }
}
