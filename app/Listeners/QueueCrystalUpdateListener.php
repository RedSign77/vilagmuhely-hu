<?php

namespace App\Listeners;

use App\Events\AchievementUnlockedEvent;
use App\Models\CrystalActivityQueue;
use Webtechsolutions\ContentEngine\Events\ContentDownloadedEvent;
use Webtechsolutions\ContentEngine\Events\ContentPublishedEvent;
use Webtechsolutions\ContentEngine\Events\ContentRatedEvent;
use Webtechsolutions\ContentEngine\Events\ContentReviewedEvent;
use Webtechsolutions\ContentEngine\Events\ContentViewedEvent;

class QueueCrystalUpdateListener
{
    /**
     * Handle content published event
     */
    public function handleContentPublished(ContentPublishedEvent $event): void
    {
        CrystalActivityQueue::addActivity(
            userId: $event->content->creator_id,
            activityType: CrystalActivityQueue::TYPE_CONTENT_PUBLISHED,
            metadata: ['content_id' => $event->content->id]
        );
    }

    /**
     * Handle content viewed event
     */
    public function handleContentViewed(ContentViewedEvent $event): void
    {
        CrystalActivityQueue::addActivity(
            userId: $event->content->creator_id,
            activityType: CrystalActivityQueue::TYPE_CONTENT_VIEWED,
            metadata: ['content_id' => $event->content->id]
        );
    }

    /**
     * Handle content downloaded event
     */
    public function handleContentDownloaded(ContentDownloadedEvent $event): void
    {
        CrystalActivityQueue::addActivity(
            userId: $event->content->creator_id,
            activityType: CrystalActivityQueue::TYPE_CONTENT_DOWNLOADED,
            metadata: ['content_id' => $event->content->id]
        );
    }

    /**
     * Handle content rated event
     */
    public function handleContentRated(ContentRatedEvent $event): void
    {
        // Update creator's metrics
        CrystalActivityQueue::addActivity(
            userId: $event->content->creator_id,
            activityType: CrystalActivityQueue::TYPE_CONTENT_RATED,
            metadata: [
                'content_id' => $event->content->id,
                'rating_id' => $event->rating->id,
                'rating_value' => $event->rating->rating,
            ]
        );

        // Also update the rater's engagement score
        CrystalActivityQueue::addActivity(
            userId: $event->rating->user_id,
            activityType: CrystalActivityQueue::TYPE_CONTENT_RATED,
            metadata: [
                'content_id' => $event->content->id,
                'rating_id' => $event->rating->id,
                'is_rater' => true,
            ]
        );
    }

    /**
     * Handle content reviewed event
     */
    public function handleContentReviewed(ContentReviewedEvent $event): void
    {
        // Update creator's metrics
        CrystalActivityQueue::addActivity(
            userId: $event->content->creator_id,
            activityType: CrystalActivityQueue::TYPE_CONTENT_REVIEWED,
            metadata: [
                'content_id' => $event->content->id,
                'review_id' => $event->review->id,
            ]
        );

        // Also update the reviewer's engagement score
        CrystalActivityQueue::addActivity(
            userId: $event->review->user_id,
            activityType: CrystalActivityQueue::TYPE_CONTENT_REVIEWED,
            metadata: [
                'content_id' => $event->content->id,
                'review_id' => $event->review->id,
                'is_reviewer' => true,
            ]
        );
    }

    /**
     * Handle achievement unlocked event
     */
    public function handleAchievementUnlocked(AchievementUnlockedEvent $event): void
    {
        CrystalActivityQueue::addActivity(
            userId: $event->user->id,
            activityType: CrystalActivityQueue::TYPE_ACHIEVEMENT_UNLOCKED,
            metadata: [
                'achievement_type' => $event->achievementType,
                'metadata' => $event->metadata,
            ]
        );
    }
}
