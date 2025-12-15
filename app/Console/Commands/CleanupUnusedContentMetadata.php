<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webtechsolutions\ContentEngine\Models\ContentCategory;
use Webtechsolutions\ContentEngine\Models\ContentTag;

class CleanupUnusedContentMetadata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:cleanup-unused-metadata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove custom categories and tags not connected to any content';

    /**
     * Seeded category slugs that should never be deleted
     */
    private const SEEDED_CATEGORY_SLUGS = [
        'tutorials-guides',
        'digital-resources',
        'visual-art',
        'news-updates',
        'rpg-content',
        'community',
        'tools-resources',
        'lore-worldbuilding',
    ];

    /**
     * Seeded tag slugs that should never be deleted
     */
    private const SEEDED_TAG_SLUGS = [
        'beginner-friendly', 'intermediate', 'advanced', 'expert',
        'free', 'premium', 'featured', 'community-choice', 'editors-pick', 'trending',
        'tutorial', 'reference', 'inspiration', 'quick-tips', 'in-depth',
        'template', 'tools', 'printable', 'editable',
        'character-art', 'maps', 'tokens', 'portraits', 'landscapes', 'icons',
        'worldbuilding', 'rules', 'adventures', 'npcs', 'encounters', 'lore', 'campaign', 'one-shot',
        'fantasy', 'sci-fi', 'horror', 'modern', 'historical', 'post-apocalyptic',
        'dnd-5e', 'pathfinder', 'osr', 'system-agnostic',
        'series', 'updated', 'wip', 'completed', 'collaborative',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting cleanup of unused content metadata...');

        // Cleanup unused custom categories
        $deletedCategories = $this->cleanupCategories();
        $this->info("Deleted {$deletedCategories} unused custom categories.");

        // Cleanup unused custom tags
        $deletedTags = $this->cleanupTags();
        $this->info("Deleted {$deletedTags} unused custom tags.");

        $this->info('Cleanup completed successfully!');

        return Command::SUCCESS;
    }

    /**
     * Clean up unused custom categories
     */
    private function cleanupCategories(): int
    {
        $categories = ContentCategory::whereNotIn('slug', self::SEEDED_CATEGORY_SLUGS)
            ->doesntHave('contents')
            ->get();

        $count = $categories->count();

        if ($count > 0) {
            $this->comment("Found {$count} unused custom categories:");
            foreach ($categories as $category) {
                $this->line("  - {$category->name} (slug: {$category->slug})");
            }

            foreach ($categories as $category) {
                $category->delete();
            }
        }

        return $count;
    }

    /**
     * Clean up unused custom tags
     */
    private function cleanupTags(): int
    {
        $tags = ContentTag::whereNotIn('slug', self::SEEDED_TAG_SLUGS)
            ->doesntHave('contents')
            ->get();

        $count = $tags->count();

        if ($count > 0) {
            $this->comment("Found {$count} unused custom tags:");
            foreach ($tags as $tag) {
                $this->line("  - {$tag->name} (slug: {$tag->slug})");
            }

            foreach ($tags as $tag) {
                $tag->delete();
            }
        }

        return $count;
    }
}
