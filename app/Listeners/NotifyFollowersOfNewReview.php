<?php

namespace App\Listeners;

use App\Notifications\NewReviewFromFollowedUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Webtechsolutions\ContentEngine\Events\ContentReviewedEvent;

class NotifyFollowersOfNewReview implements ShouldQueue
{
    public function handle(ContentReviewedEvent $event): void
    {
        $review = $event->review;
        $content = $event->content;
        $reviewer = $review->user;

        if (! $reviewer || $review->status !== 'approved') {
            return; // Only notify on approved reviews
        }

        // Notify followers
        $reviewer->followers()
            ->chunk(100, function ($followers) use ($review, $content, $reviewer) {
                Notification::send(
                    $followers,
                    new NewReviewFromFollowedUser($review, $content, $reviewer)
                );
            });
    }
}
