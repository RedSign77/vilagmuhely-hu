<?php

namespace Webtechsolutions\ContentEngine\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Webtechsolutions\ContentEngine\Events\ContentPublishedEvent;
use Webtechsolutions\ContentEngine\Models\WorldActivityLog;
use Webtechsolutions\ContentEngine\Services\WorldResourceService;

class AwardResourcesOnContentPublished implements ShouldQueue
{
    protected WorldResourceService $resourceService;

    public function __construct(WorldResourceService $resourceService)
    {
        $this->resourceService = $resourceService;
    }

    /**
     * Handle the event
     */
    public function handle(ContentPublishedEvent $event): void
    {
        $content = $event->content;
        $user = $content->creator;

        if (!$user) {
            return;
        }

        // Award resources based on content type
        $resources = $this->resourceService->awardContentResources($user, $content);

        // Log the resource gain
        WorldActivityLog::log(
            $user->id,
            WorldActivityLog::TYPE_RESOURCE_EARNED,
            null,
            [
                'source' => 'content_published',
                'content_id' => $content->id,
                'content_type' => $content->type,
                'content_title' => $content->title,
                'resources_earned' => $resources,
            ]
        );
    }
}
