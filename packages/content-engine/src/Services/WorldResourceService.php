<?php

namespace Webtechsolutions\ContentEngine\Services;

use App\Models\User;
use Webtechsolutions\ContentEngine\Models\Content;
use Webtechsolutions\ContentEngine\Models\UserWorldResource;
use Webtechsolutions\ContentEngine\Models\WorldStructure;

class WorldResourceService
{
    /**
     * Award resources based on content type
     */
    public function awardContentResources(User $user, Content $content): array
    {
        $resources = match ($content->type) {
            Content::TYPE_DIGITAL_FILE => ['stone' => 5, 'wood' => 2, 'crystal_shards' => 3],
            Content::TYPE_IMAGE_GALLERY => ['stone' => 2, 'wood' => 5, 'crystal_shards' => 3],
            Content::TYPE_MARKDOWN_POST => ['stone' => 3, 'wood' => 5, 'crystal_shards' => 2],
            Content::TYPE_ARTICLE => ['stone' => 5, 'wood' => 5, 'crystal_shards' => 2, 'magic_essence' => 1],
            Content::TYPE_RPG_MODULE => ['stone' => 3, 'wood' => 3, 'crystal_shards' => 5, 'magic_essence' => 3],
            default => ['stone' => 1, 'wood' => 1],
        };

        $this->addResources($user, $resources);

        return $resources;
    }

    /**
     * Award resources for engagement actions
     */
    public function awardEngagementResources(User $user, string $action, array $metadata = []): array
    {
        $resources = match ($action) {
            'rating_given' => ['wood' => 1],
            'rating_received' => ['crystal_shards' => $metadata['rating'] ?? 1],
            'content_viewed_batch' => ['stone' => 1], // Per 10 views
            'content_downloaded' => ['crystal_shards' => 1],
            'helpful_rating' => ['crystal_shards' => 2, 'magic_essence' => 1],
            default => [],
        };

        if (! empty($resources)) {
            $this->addResources($user, $resources);
        }

        return $resources;
    }

    /**
     * Check if user can afford structure
     */
    public function canAfford(User $user, string $structureType): bool
    {
        $costs = WorldStructure::getStructureCosts($structureType);
        $userResources = $this->getResources($user);

        return $userResources->canAfford($costs);
    }

    /**
     * Spend resources for building
     */
    public function spendResources(User $user, array $costs): bool
    {
        $userResources = $this->getResources($user);

        return $userResources->spendResources($costs);
    }

    /**
     * Get user's current resources
     */
    public function getResources(User $user): UserWorldResource
    {
        return UserWorldResource::firstOrCreate(
            ['user_id' => $user->id],
            [
                'stone' => 0,
                'wood' => 0,
                'crystal_shards' => 10, // Starting resources
                'magic_essence' => 0,
            ]
        );
    }

    /**
     * Add resources to user
     */
    protected function addResources(User $user, array $resources): void
    {
        $userResources = $this->getResources($user);
        $userResources->addResources($resources);
    }

    /**
     * Get affordable structures for user
     */
    public function getAffordableStructures(User $user): array
    {
        $userResources = $this->getResources($user);
        $affordable = [];

        $structureTypes = [
            WorldStructure::TYPE_COTTAGE,
            WorldStructure::TYPE_WORKSHOP,
            WorldStructure::TYPE_GALLERY,
            WorldStructure::TYPE_LIBRARY,
            WorldStructure::TYPE_ACADEMY,
            WorldStructure::TYPE_TOWER,
            WorldStructure::TYPE_GARDEN,
        ];

        foreach ($structureTypes as $type) {
            $costs = WorldStructure::getStructureCosts($type);
            if ($userResources->canAfford($costs)) {
                $affordable[] = $type;
            }
        }

        return $affordable;
    }

    /**
     * Get resource summary for API
     */
    public function getResourceSummary(User $user): array
    {
        $userResources = $this->getResources($user);
        $affordable = $this->getAffordableStructures($user);

        return [
            'resources' => $userResources->toArray(),
            'can_build' => $affordable,
            'total_value' => $userResources->total_resources,
        ];
    }
}
