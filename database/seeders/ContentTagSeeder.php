<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Webtechsolutions\ContentEngine\Models\ContentTag;

class ContentTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            // Difficulty Level Tags
            ['name' => 'Beginner Friendly', 'slug' => 'beginner-friendly', 'color' => '#10b981'],
            ['name' => 'Intermediate', 'slug' => 'intermediate', 'color' => '#f59e0b'],
            ['name' => 'Advanced', 'slug' => 'advanced', 'color' => '#ef4444'],
            ['name' => 'Expert', 'slug' => 'expert', 'color' => '#8b5cf6'],

            // Access & Quality Tags
            ['name' => 'Free', 'slug' => 'free', 'color' => '#10b981'],
            ['name' => 'Premium', 'slug' => 'premium', 'color' => '#f59e0b'],
            ['name' => 'Featured', 'slug' => 'featured', 'color' => '#ec4899'],
            ['name' => 'Community Choice', 'slug' => 'community-choice', 'color' => '#8b5cf6'],
            ['name' => 'Editor\'s Pick', 'slug' => 'editors-pick', 'color' => '#6366f1'],
            ['name' => 'Trending', 'slug' => 'trending', 'color' => '#f43f5e'],

            // Content Type Related Tags
            ['name' => 'Tutorial', 'slug' => 'tutorial', 'color' => '#10b981'],
            ['name' => 'Reference', 'slug' => 'reference', 'color' => '#3b82f6'],
            ['name' => 'Inspiration', 'slug' => 'inspiration', 'color' => '#ec4899'],
            ['name' => 'Quick Tips', 'slug' => 'quick-tips', 'color' => '#06b6d4'],
            ['name' => 'In-Depth', 'slug' => 'in-depth', 'color' => '#8b5cf6'],

            // Digital File Tags
            ['name' => 'Template', 'slug' => 'template', 'color' => '#6366f1'],
            ['name' => 'Tools', 'slug' => 'tools', 'color' => '#06b6d4'],
            ['name' => 'Printable', 'slug' => 'printable', 'color' => '#10b981'],
            ['name' => 'Editable', 'slug' => 'editable', 'color' => '#f59e0b'],

            // Visual Art Tags
            ['name' => 'Character Art', 'slug' => 'character-art', 'color' => '#ec4899'],
            ['name' => 'Maps', 'slug' => 'maps', 'color' => '#10b981'],
            ['name' => 'Tokens', 'slug' => 'tokens', 'color' => '#f59e0b'],
            ['name' => 'Portraits', 'slug' => 'portraits', 'color' => '#8b5cf6'],
            ['name' => 'Landscapes', 'slug' => 'landscapes', 'color' => '#06b6d4'],
            ['name' => 'Icons', 'slug' => 'icons', 'color' => '#3b82f6'],

            // RPG Content Tags
            ['name' => 'Worldbuilding', 'slug' => 'worldbuilding', 'color' => '#84cc16'],
            ['name' => 'Rules', 'slug' => 'rules', 'color' => '#f59e0b'],
            ['name' => 'Adventures', 'slug' => 'adventures', 'color' => '#ec4899'],
            ['name' => 'NPCs', 'slug' => 'npcs', 'color' => '#8b5cf6'],
            ['name' => 'Encounters', 'slug' => 'encounters', 'color' => '#ef4444'],
            ['name' => 'Lore', 'slug' => 'lore', 'color' => '#06b6d4'],
            ['name' => 'Campaign', 'slug' => 'campaign', 'color' => '#3b82f6'],
            ['name' => 'One-Shot', 'slug' => 'one-shot', 'color' => '#10b981'],

            // Genre Tags
            ['name' => 'Fantasy', 'slug' => 'fantasy', 'color' => '#8b5cf6'],
            ['name' => 'Sci-Fi', 'slug' => 'sci-fi', 'color' => '#06b6d4'],
            ['name' => 'Horror', 'slug' => 'horror', 'color' => '#ef4444'],
            ['name' => 'Modern', 'slug' => 'modern', 'color' => '#64748b'],
            ['name' => 'Historical', 'slug' => 'historical', 'color' => '#92400e'],
            ['name' => 'Post-Apocalyptic', 'slug' => 'post-apocalyptic', 'color' => '#78350f'],

            // System Tags (for RPG systems)
            ['name' => 'D&D 5e', 'slug' => 'dnd-5e', 'color' => '#ef4444'],
            ['name' => 'Pathfinder', 'slug' => 'pathfinder', 'color' => '#f59e0b'],
            ['name' => 'OSR', 'slug' => 'osr', 'color' => '#10b981'],
            ['name' => 'System Agnostic', 'slug' => 'system-agnostic', 'color' => '#64748b'],

            // Special Tags
            ['name' => 'Series', 'slug' => 'series', 'color' => '#6366f1'],
            ['name' => 'Updated', 'slug' => 'updated', 'color' => '#10b981'],
            ['name' => 'Work in Progress', 'slug' => 'wip', 'color' => '#f59e0b'],
            ['name' => 'Completed', 'slug' => 'completed', 'color' => '#10b981'],
            ['name' => 'Collaborative', 'slug' => 'collaborative', 'color' => '#8b5cf6'],
        ];

        foreach ($tags as $tag) {
            ContentTag::firstOrCreate(
                ['slug' => $tag['slug']],
                $tag
            );
        }

        $this->command->info('Content tags seeded successfully.');
    }
}
