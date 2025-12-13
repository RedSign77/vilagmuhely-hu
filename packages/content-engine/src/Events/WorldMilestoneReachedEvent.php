<?php

namespace Webtechsolutions\ContentEngine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorldMilestoneReachedEvent
{
    use Dispatchable, SerializesModels;

    public string $milestoneType;

    public array $stats;

    public function __construct(string $milestoneType, array $stats)
    {
        $this->milestoneType = $milestoneType;
        $this->stats = $stats;
    }
}
