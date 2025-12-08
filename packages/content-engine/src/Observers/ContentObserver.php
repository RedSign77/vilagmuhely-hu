<?php

namespace Webtechsolutions\ContentEngine\Observers;

use Webtechsolutions\ContentEngine\Events\ContentPublishedEvent;
use Webtechsolutions\ContentEngine\Models\Content;

class ContentObserver
{
    /**
     * Handle the Content "created" event.
     */
    public function created(Content $content): void
    {
        // If content is published when created, award resources
        if ($content->status === Content::STATUS_PUBLIC || $content->status === Content::STATUS_MEMBERS_ONLY) {
            if ($content->published_at === null) {
                $content->published_at = now();
                $content->saveQuietly(); // Save without triggering events again
            }
            ContentPublishedEvent::dispatch($content);
        }
    }

    /**
     * Handle the Content "updated" event.
     */
    public function updated(Content $content): void
    {
        // Check if status changed to published
        if ($content->isDirty('status')) {
            $oldStatus = $content->getOriginal('status');
            $newStatus = $content->status;

            // If status changed from draft/preview to published, award resources
            // Only award if this is the FIRST time being published (published_at is null)
            if (
                in_array($oldStatus, [Content::STATUS_DRAFT, Content::STATUS_PREVIEW])
                && in_array($newStatus, [Content::STATUS_PUBLIC, Content::STATUS_MEMBERS_ONLY])
                && $content->published_at === null
            ) {
                $content->published_at = now();
                $content->saveQuietly(); // Save without triggering events again
                ContentPublishedEvent::dispatch($content);
            }
        }
    }
}
