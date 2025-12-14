<?php

namespace Webtechsolutions\ContentEngine\Events;

use App\Models\ContentReview;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webtechsolutions\ContentEngine\Models\Content;

class ContentReviewedEvent
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Content $content,
        public ContentReview $review
    ) {
        //
    }
}
