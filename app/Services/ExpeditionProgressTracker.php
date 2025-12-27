<?php

namespace App\Services;

use App\Models\Expedition;
use App\Models\ExpeditionEnrollment;
use App\Models\ExpeditionQualifyingPost;
use Illuminate\Support\Str;
use Webtechsolutions\BlogPackage\Models\Post;

class ExpeditionProgressTracker
{
    /**
     * Track a newly published post against all active expeditions
     */
    public function trackNewPost(Post $post): void
    {
        // Get all active expeditions
        $activeExpeditions = Expedition::active()->get();

        foreach ($activeExpeditions as $expedition) {
            $this->checkPostAgainstExpedition($post, $expedition);
        }
    }

    /**
     * Check if a post qualifies for an expedition
     */
    public function checkPostAgainstExpedition(Post $post, Expedition $expedition): void
    {
        // Get user's enrollment
        $enrollment = ExpeditionEnrollment::where('expedition_id', $expedition->id)
            ->where('user_id', $post->author_id)
            ->whereNull('completed_at')
            ->first();

        if (!$enrollment) {
            return; // User not enrolled
        }

        // Check if post already qualified
        if (ExpeditionQualifyingPost::where('expedition_id', $expedition->id)
            ->where('post_id', $post->id)
            ->exists()) {
            return; // Already qualified
        }

        // Validate requirements
        if (!$this->validatePostRequirements($post, $expedition)) {
            return; // Doesn't meet requirements
        }

        // Record qualification
        ExpeditionQualifyingPost::create([
            'expedition_id' => $expedition->id,
            'enrollment_id' => $enrollment->id,
            'post_id' => $post->id,
            'qualified_at' => now(),
        ]);

        // Update enrollment progress
        $this->updateEnrollmentProgress($enrollment, $post);

        // Check for completion
        $this->checkAndCompleteEnrollment($enrollment);
    }

    /**
     * Validate post meets expedition requirements
     */
    protected function validatePostRequirements(Post $post, Expedition $expedition): bool
    {
        $requirements = $expedition->requirements;

        // Check word count
        if (isset($requirements['min_word_count'])) {
            $wordCount = $this->getWordCount($post);
            if ($wordCount < $requirements['min_word_count']) {
                return false;
            }
        }

        // Check content type (currently only posts supported)
        if (isset($requirements['content_type']) && $requirements['content_type'] !== 'post') {
            return false;
        }

        return true;
    }

    /**
     * Get word count from post content
     */
    protected function getWordCount(Post $post): int
    {
        $text = strip_tags($post->content);
        return str_word_count($text);
    }

    /**
     * Update enrollment progress
     */
    protected function updateEnrollmentProgress(ExpeditionEnrollment $enrollment, Post $post): void
    {
        $progress = $enrollment->getProgress();
        $progress['posts_created'] = $enrollment->qualifyingPosts()->count();
        $progress['qualifying_post_ids'][] = $post->id;
        $progress['last_checked_at'] = now()->toISOString();

        $enrollment->updateProgress($progress);
    }

    /**
     * Check and complete enrollment if requirements met
     */
    protected function checkAndCompleteEnrollment(ExpeditionEnrollment $enrollment): void
    {
        if ($enrollment->checkCompletion() && !$enrollment->isCompleted()) {
            $this->processCompletion($enrollment);
        }
    }

    /**
     * Process completion and grant rewards
     */
    protected function processCompletion(ExpeditionEnrollment $enrollment): void
    {
        $enrollment->update(['completed_at' => now()]);

        // Grant rewards through service
        app(ExpeditionRewardService::class)->grantRewards($enrollment);
    }
}
