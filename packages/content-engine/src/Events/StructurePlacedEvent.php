<?php

namespace Webtechsolutions\ContentEngine\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webtechsolutions\ContentEngine\Models\WorldStructure;

class StructurePlacedEvent
{
    use Dispatchable, SerializesModels;

    public User $user;

    public WorldStructure $structure;

    public function __construct(User $user, WorldStructure $structure)
    {
        $this->user = $user;
        $this->structure = $structure;
    }
}
