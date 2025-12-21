<?php

namespace App\Services;

use Webtechsolutions\ContentEngine\Models\Content;

class MilestoneTrackerService
{
    /**
     * Check if content has reached a new views milestone
     *
     * @return int|null The milestone reached, or null if none
     */
    public function checkViewsMilestone(Content $content): ?int
    {
        $currentViews = $content->views_count;
        $milestones = config('crystals.milestones.views', []);
        $reachedMilestones = $this->getMilestones($content, 'views');

        foreach ($milestones as $milestone) {
            if ($currentViews >= $milestone && !in_array($milestone, $reachedMilestones)) {
                return $milestone;
            }
        }

        return null;
    }

    /**
     * Check if content has reached a new downloads milestone
     *
     * @return int|null The milestone reached, or null if none
     */
    public function checkDownloadsMilestone(Content $content): ?int
    {
        $currentDownloads = $content->downloads_count;
        $milestones = config('crystals.milestones.downloads', []);
        $reachedMilestones = $this->getMilestones($content, 'downloads');

        foreach ($milestones as $milestone) {
            if ($currentDownloads >= $milestone && !in_array($milestone, $reachedMilestones)) {
                return $milestone;
            }
        }

        return null;
    }

    /**
     * Check if content has reached the rating threshold (3+ ratings)
     *
     * @return bool True if threshold just reached, false otherwise
     */
    public function checkRatingThreshold(Content $content): bool
    {
        $threshold = config('crystals.milestones.rating_threshold', 3);
        $ratingCount = $content->ratings()->count();

        $milestones = $content->milestones_reached ?? [];
        $thresholdReached = $milestones['rating_threshold'] ?? false;

        return $ratingCount >= $threshold && !$thresholdReached;
    }

    /**
     * Mark a milestone as reached for the content
     *
     * @param Content $content
     * @param string $type Milestone type: 'views', 'downloads', 'first_review', 'rating_threshold'
     * @param mixed $value The milestone value (int for views/downloads, bool for others)
     */
    public function markMilestoneReached(Content $content, string $type, mixed $value): void
    {
        $milestones = $content->milestones_reached ?? [];

        if ($type === 'views' || $type === 'downloads') {
            if (!isset($milestones[$type])) {
                $milestones[$type] = [];
            }
            if (!in_array($value, $milestones[$type])) {
                $milestones[$type][] = $value;
                sort($milestones[$type]);
            }
        } else {
            $milestones[$type] = $value;
        }

        $content->milestones_reached = $milestones;
        $content->save();
    }

    /**
     * Get already reached milestones for a specific type
     *
     * @param Content $content
     * @param string $type
     * @return array
     */
    protected function getMilestones(Content $content, string $type): array
    {
        $milestones = $content->milestones_reached ?? [];
        return $milestones[$type] ?? [];
    }
}
