<?php

namespace Webtechsolutions\ContentEngine\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResourcesEarnedEvent
{
    use Dispatchable, SerializesModels;

    public User $user;
    public array $resources;
    public string $source;

    public function __construct(User $user, array $resources, string $source)
    {
        $this->user = $user;
        $this->resources = $resources;
        $this->source = $source;
    }
}
