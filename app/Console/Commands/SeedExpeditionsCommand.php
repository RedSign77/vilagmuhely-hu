<?php

namespace App\Console\Commands;

use App\Models\Expedition;
use Illuminate\Console\Command;

class SeedExpeditionsCommand extends Command
{
    protected $signature = 'expedition:seed {--count=3 : Number of expeditions to create}';

    protected $description = 'Create sample expeditions for testing';

    public function handle()
    {
        $count = $this->option('count');

        $this->info("Creating {$count} sample expeditions...");

        $expeditions = [
            [
                'title' => 'Winter Writing Challenge',
                'description' => 'Create engaging blog posts this winter season. Share your knowledge, stories, and creativity with the community.',
                'starts_at' => now(),
                'ends_at' => now()->addDays(14),
                'status' => 'active',
                'requirements' => [
                    'content_type' => 'post',
                    'min_word_count' => 500,
                    'required_count' => 3,
                ],
                'rewards' => [
                    'crystal_multiplier' => 2.0,
                    'engagement_bonus' => 100,
                    'interaction_bonus' => 50,
                    'visual_effect' => 'expedition_winner_aura',
                    'effect_duration_days' => 30,
                ],
                'max_participants' => null,
            ],
            [
                'title' => 'Knowledge Sharing Sprint',
                'description' => 'Share your expertise with the community. Write tutorials, guides, or educational content.',
                'starts_at' => now()->addDays(7),
                'ends_at' => now()->addDays(21),
                'status' => 'active',
                'requirements' => [
                    'content_type' => 'post',
                    'min_word_count' => 750,
                    'required_count' => 5,
                ],
                'rewards' => [
                    'crystal_multiplier' => 2.5,
                    'engagement_bonus' => 150,
                    'interaction_bonus' => 75,
                    'visual_effect' => 'crystal_surge',
                    'effect_duration_days' => 7,
                ],
                'max_participants' => 50,
            ],
            [
                'title' => 'Creative Writing Marathon',
                'description' => 'Push your creative limits. Write, create, and inspire!',
                'starts_at' => now()->addDays(14),
                'ends_at' => now()->addDays(30),
                'status' => 'active',
                'requirements' => [
                    'content_type' => 'post',
                    'min_word_count' => 1000,
                    'required_count' => 10,
                ],
                'rewards' => [
                    'crystal_multiplier' => 3.0,
                    'engagement_bonus' => 200,
                    'interaction_bonus' => 100,
                    'visual_effect' => 'spectral_shimmer',
                    'effect_duration_days' => 14,
                ],
                'max_participants' => 25,
            ],
        ];

        $created = 0;
        foreach (array_slice($expeditions, 0, $count) as $expeditionData) {
            Expedition::create($expeditionData);
            $created++;
        }

        $this->info("Created {$created} expeditions successfully!");

        return Command::SUCCESS;
    }
}
