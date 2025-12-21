<?php

namespace App\Observers;

use App\Models\Post;

class PostObserver
{
    /**
     * Handle the Post "creating" event.
     */
    public function creating(Post $post): void
    {
        $this->generateSeoMetadata($post);
    }

    /**
     * Handle the Post "updating" event.
     */
    public function updating(Post $post): void
    {
        $this->generateSeoMetadata($post);
    }

    /**
     * Generate SEO metadata from post content.
     */
    protected function generateSeoMetadata(Post $post): void
    {
        // Auto-generate meta_title if empty
        if (empty($post->meta_title)) {
            $post->meta_title = $post->title;
        }

        // Auto-generate meta_description if empty
        if (empty($post->meta_description)) {
            // Strip HTML tags first
            $cleanContent = strip_tags($post->content);
            // Remove extra whitespace and newlines
            $cleanContent = preg_replace('/\s+/', ' ', $cleanContent);
            // Trim and limit to 160 characters WITHOUT adding '...'
            $cleanContent = trim($cleanContent);
            $post->meta_description = mb_substr($cleanContent, 0, 160);
        }

        // Set published_at if status changed to published and published_at is empty
        if ($post->status === 'published' && empty($post->published_at)) {
            $post->published_at = now();
        }
    }

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        //
    }

    /**
     * Handle the Post "restored" event.
     */
    public function restored(Post $post): void
    {
        //
    }

    /**
     * Handle the Post "force deleted" event.
     */
    public function forceDeleted(Post $post): void
    {
        //
    }
}
