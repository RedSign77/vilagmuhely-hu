<?php

namespace App\Console\Commands;

use App\Models\WorldMapConfig;
use App\Services\WorldGenerationService;
use Illuminate\Console\Command;

class GenerateWorldElements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world:generate-elements
                            {--regenerate : Clear existing elements and regenerate}
                            {--biome= : Generate elements for specific biome only}
                            {--density=medium : Density multiplier (low, medium, high)}
                            {--seed= : Random seed for reproducible generation}
                            {--dry-run : Preview generation without saving}
                            {--stats : Show generation statistics after completion}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate world map elements with procedural placement';

    protected WorldGenerationService $generationService;

    public function __construct(WorldGenerationService $generationService)
    {
        parent::__construct();
        $this->generationService = $generationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🌍 World Element Generation');
        $this->newLine();

        // Get options
        $regenerate = $this->option('regenerate');
        $biome = $this->option('biome');
        $density = $this->option('density');
        $seed = $this->option('seed');
        $dryRun = $this->option('dry-run');
        $showStats = $this->option('stats');

        // Validate density option
        if (!in_array($density, ['low', 'medium', 'high'])) {
            $this->error('Invalid density option. Must be: low, medium, or high');

            return self::FAILURE;
        }

        // Validate biome option
        $validBiomes = ['forest', 'meadow', 'desert', 'tundra', 'swamp'];
        if ($biome && !in_array($biome, $validBiomes)) {
            $this->error('Invalid biome. Valid options: '.implode(', ', $validBiomes));

            return self::FAILURE;
        }

        // Show configuration
        $config = WorldMapConfig::getInstance();
        $this->table(
            ['Setting', 'Value'],
            [
                ['Map Size', "{$config->map_width} × {$config->map_height}"],
                ['Tile Size', "{$config->tile_size}px"],
                ['Regenerate', $regenerate ? 'Yes' : 'No'],
                ['Biome Filter', $biome ?? 'All'],
                ['Density', ucfirst($density)],
                ['Seed', $seed ?? 'Random'],
                ['Dry Run', $dryRun ? 'Yes' : 'No'],
            ]
        );

        $this->newLine();

        // Confirmation for regeneration
        if ($regenerate && !$dryRun) {
            $currentCount = \App\Models\WorldElementInstance::count();
            $this->warn("⚠️  This will DELETE {$currentCount} existing elements!");

            if (!$this->confirm('Are you sure you want to regenerate the map?', false)) {
                $this->info('Generation cancelled.');

                return self::SUCCESS;
            }
        }

        // Dry run preview
        if ($dryRun) {
            $this->info('🔍 Dry run mode - No elements will be created');
            $this->newLine();

            // Show biome distribution
            $this->info('Biome Distribution:');
            $biomeStats = $this->generationService->generateBiomeMap($config->map_width, $config->map_height);
            $this->table(
                ['Biome', 'Cells'],
                collect($biomeStats)->map(fn ($count, $biome) => [ucfirst($biome), $count])->values()->toArray()
            );

            return self::SUCCESS;
        }

        // Start generation
        $this->info('🚀 Generating elements...');
        $progressBar = $this->output->createProgressBar(100);
        $progressBar->start();

        try {
            $result = $this->generationService->generateElements([
                'regenerate' => $regenerate,
                'biome' => $biome,
                'density' => $density,
                'seed' => $seed,
            ]);

            $progressBar->finish();
            $this->newLine(2);

            if ($result['success']) {
                $this->info("✅ {$result['message']}");
                $this->newLine();

                // Show statistics
                if (isset($result['stats']) && $result['stats']['total_generated'] > 0) {
                    $this->info('📊 Generation Statistics:');
                    $this->newLine();

                    // By Category
                    if (!empty($result['stats']['by_category'])) {
                        $this->info('By Category:');
                        $this->table(
                            ['Category', 'Count'],
                            collect($result['stats']['by_category'])
                                ->map(fn ($count, $category) => [ucfirst($category), $count])
                                ->values()
                                ->toArray()
                        );
                    }

                    // By Biome
                    if (!empty($result['stats']['by_biome'])) {
                        $this->info('By Biome:');
                        $this->table(
                            ['Biome', 'Count'],
                            collect($result['stats']['by_biome'])
                                ->map(fn ($count, $biome) => [ucfirst($biome), $count])
                                ->values()
                                ->toArray()
                        );
                    }

                    // By Rarity
                    if (!empty($result['stats']['by_rarity'])) {
                        $this->info('By Rarity:');
                        $this->table(
                            ['Rarity', 'Count'],
                            collect($result['stats']['by_rarity'])
                                ->map(fn ($count, $rarity) => [ucfirst($rarity), $count])
                                ->values()
                                ->toArray()
                        );
                    }
                }
            } else {
                $this->error("❌ {$result['message']}");

                return self::FAILURE;
            }

            // Show additional stats if requested
            if ($showStats) {
                $this->newLine();
                $this->info('📈 Overall Map Statistics:');
                $allStats = $this->generationService->getGenerationStats();
                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Total Elements', $allStats['total_elements']],
                        ['Last Regenerated', $allStats['last_regenerated'] ?? 'Never'],
                        ['Generation Seed', $allStats['generation_seed'] ?? 'N/A'],
                    ]
                );
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $progressBar->finish();
            $this->newLine(2);
            $this->error('❌ Generation failed: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return self::FAILURE;
        }
    }
}

