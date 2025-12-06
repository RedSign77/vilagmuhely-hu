<?php

namespace Webtechsolutions\ContentEngine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webtechsolutions\ContentEngine\Models\Content;
use Webtechsolutions\ContentEngine\Models\ContentRating;

class ContentRatedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Content $content,
        public ContentRating $rating
    ) {}
}
