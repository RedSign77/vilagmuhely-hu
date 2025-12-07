<?php

namespace Webtechsolutions\ContentEngine\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webtechsolutions\ContentEngine\Models\WorldStructure;

class StructureUpgradedEvent
{
    use Dispatchable, SerializesModels;

    public User $user;
    public WorldStructure $structure;
    public int $previousLevel;

    public function __construct(User $user, WorldStructure $structure, int $previousLevel)
    {
        $this->user = $user;
        $this->structure = $structure;
        $this->previousLevel = $previousLevel;
    }
}
