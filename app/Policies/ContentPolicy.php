<?php

namespace App\Policies;

use App\Models\ContentDownload;
use App\Models\ContentReview;
use App\Models\User;
use Webtechsolutions\ContentEngine\Models\Content;

class ContentPolicy
{
    /**
     * Determine if the user can download the content.
     */
    public function download(User $user, Content $content): bool
    {
        // Content must be published
        if (! $content->isPublished()) {
            return false;
        }

        // Content must be public or members-only
        if (! in_array($content->status, [Content::STATUS_PUBLIC, Content::STATUS_MEMBERS_ONLY])) {
            return false;
        }

        // User must not have already downloaded it
        if (ContentDownload::hasUserDownloaded($content->id, $user->id)) {
            return false;
        }

        // Content must have a file
        if (empty($content->file_path)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can rate the content.
     */
    public function rate(User $user, Content $content): bool
    {
        // User must have downloaded the content first
        if (! ContentDownload::hasUserDownloaded($content->id, $user->id)) {
            return false;
        }

        // Cannot rate own content
        if ($content->creator_id === $user->id) {
            return false;
        }

        // Cannot rate if already rated
        if ($content->hasBeenRatedBy($user->id)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can review the content.
     */
    public function review(User $user, Content $content): bool
    {
        // User must have downloaded the content first
        if (! ContentDownload::hasUserDownloaded($content->id, $user->id)) {
            return false;
        }

        // Cannot review own content
        if ($content->creator_id === $user->id) {
            return false;
        }

        // Cannot review if already reviewed
        if ($content->hasBeenReviewedBy($user->id)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can update the review.
     */
    public function updateReview(User $user, ContentReview $review): bool
    {
        // Only the review owner can update it
        return $review->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the review.
     */
    public function deleteReview(User $user, ContentReview $review): bool
    {
        // Review owner can delete
        if ($review->user_id === $user->id) {
            return true;
        }

        // Supervisors can delete
        if ($user->isSupervisor()) {
            return true;
        }

        return false;
    }
}
