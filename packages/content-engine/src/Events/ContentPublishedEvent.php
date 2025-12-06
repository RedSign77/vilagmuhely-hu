<?php

namespace Webtechsolutions\ContentEngine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webtechsolutions\ContentEngine\Models\Content;

class ContentPublishedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Content $content
    ) {}
}
