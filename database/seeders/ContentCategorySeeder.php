<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Webtechsolutions\ContentEngine\Models\ContentCategory;

class ContentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Tutorials & Guides',
                'slug' => 'tutorials-guides',
                'description' => 'Step-by-step guides, how-tos, and educational content to help you learn and master new skills.',
                'color' => '#10b981', // Green
                'icon' => 'heroicon-o-academic-cap',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Digital Resources',
                'slug' => 'digital-resources',
                'description' => 'Downloadable files, PDFs, templates, and digital assets for your projects.',
                'color' => '#6366f1', // Indigo
                'icon' => 'heroicon-o-document-arrow-down',
                'sort_order' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Visual Art & Galleries',
                'slug' => 'visual-art',
                'description' => 'Image collections, character art, maps, inspiration boards, and visual content.',
                'color' => '#ec4899', // Pink
                'icon' => 'heroicon-o-photo',
                'sort_order' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'News & Updates',
                'slug' => 'news-updates',
                'description' => 'Latest news, announcements, community updates, and blog posts.',
                'color' => '#3b82f6', // Blue
                'icon' => 'heroicon-o-newspaper',
                'sort_order' => 40,
                'is_active' => true,
            ],
            [
                'name' => 'RPG Content',
                'slug' => 'rpg-content',
                'description' => 'RPG modules, character sheets, campaign settings, worldbuilding resources, and game materials.',
                'color' => '#f59e0b', // Amber
                'icon' => 'heroicon-o-sparkles',
                'sort_order' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Community Creations',
                'slug' => 'community',
                'description' => 'User-generated content, community contributions, and collaborative projects.',
                'color' => '#8b5cf6', // Purple
                'icon' => 'heroicon-o-users',
                'sort_order' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Tools & Resources',
                'slug' => 'tools-resources',
                'description' => 'Helpful tools, generators, calculators, and productivity resources.',
                'color' => '#06b6d4', // Cyan
                'icon' => 'heroicon-o-wrench-screwdriver',
                'sort_order' => 70,
                'is_active' => true,
            ],
            [
                'name' => 'Lore & Worldbuilding',
                'slug' => 'lore-worldbuilding',
                'description' => 'World lore, histories, cultures, languages, and deep worldbuilding content.',
                'color' => '#84cc16', // Lime
                'icon' => 'heroicon-o-globe-alt',
                'sort_order' => 80,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ContentCategory::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('Content categories seeded successfully.');
    }
}
