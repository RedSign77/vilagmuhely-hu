<?php

namespace App\Listeners;

use App\Notifications\NewContentFromFollowedUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Webtechsolutions\ContentEngine\Events\ContentPublishedEvent;

class NotifyFollowersOfNewContent implements ShouldQueue
{
    /**
     * Handle the event
     */
    public function handle(ContentPublishedEvent $event): void
    {
        $content = $event->content;
        $creator = $content->creator;

        if (! $creator) {
            return;
        }

        // Get all followers in chunks to avoid memory issues
        $creator->followers()
            ->chunk(100, function ($followers) use ($content, $creator) {
                Notification::send(
                    $followers,
                    new NewContentFromFollowedUser($content, $creator)
                );
            });
    }
}
