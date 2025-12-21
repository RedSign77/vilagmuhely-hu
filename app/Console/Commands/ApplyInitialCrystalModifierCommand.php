<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CrystalCalculatorService;
use Illuminate\Console\Command;

class ApplyInitialCrystalModifierCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crystal:apply-initial-modifier
                            {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply initial profile completeness modifier to existing user crystals';

    protected CrystalCalculatorService $calculatorService;

    /**
     * Create a new command instance.
     */
    public function __construct(CrystalCalculatorService $calculatorService)
    {
        parent::__construct();
        $this->calculatorService = $calculatorService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be saved');
        }

        // Get all users with crystal metrics that haven't had the modifier applied
        $users = User::whereHas('crystalMetric', function ($query) {
            $query->where('initial_modifier_applied', false)
                ->orWhereNull('initial_modifier_applied');
        })->with('crystalMetric')->get();

        if ($users->isEmpty()) {
            $this->info('No users found that need modifier application.');

            return Command::SUCCESS;
        }

        $this->info("Found {$users->count()} users to process...\n");

        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        $results = [
            'processed' => 0,
            'updated' => 0,
            'errors' => 0,
        ];

        foreach ($users as $user) {
            try {
                $metric = $user->crystalMetric;

                // Calculate profile completeness
                $completeness = $this->calculatorService->calculateProfileCompleteness($user);
                $modifier = $this->calculatorService->calculateInitialModifier($completeness);
                $bonusPercent = round(($modifier - 1.0) * 100, 0);

                // Display user info
                if (! $isDryRun) {
                    $this->newLine();
                    $this->line("User: {$user->name} (ID: {$user->id})");
                    $this->line('  Profile Completeness: '.round($completeness * 100, 0).'%');
                    $this->line("  Modifier: {$modifier}x (Bonus: +{$bonusPercent}%)");

                    // Apply modifier and recalculate
                    $metric->profile_completeness_modifier = $modifier;
                    $metric->initial_modifier_applied = true;

                    // Get old scores for comparison
                    $oldInteraction = $metric->interaction_score;
                    $oldEngagement = $metric->engagement_score;

                    // Recalculate with modifier
                    $this->calculatorService->recalculateMetrics($user);

                    // Reload to get updated values
                    $metric->refresh();

                    $this->line("  Old Interaction Score: {$oldInteraction}");
                    $this->line("  New Interaction Score: {$metric->interaction_score}");
                    $this->line("  Old Engagement Score: {$oldEngagement}");
                    $this->line("  New Engagement Score: {$metric->engagement_score}");

                    $results['updated']++;
                } else {
                    // Dry run - just show what would happen
                    if ($results['processed'] === 0) {
                        $this->newLine();
                    }
                    $this->line("Would update: {$user->name} (ID: {$user->id})");
                    $this->line('  Completeness: '.round($completeness * 100, 0)."% | Modifier: {$modifier}x | Bonus: +{$bonusPercent}%");
                }

                $results['processed']++;
            } catch (\Exception $e) {
                $this->error("\nError processing user {$user->id}: ".$e->getMessage());
                $results['errors']++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('=== Summary ===');
        $this->line("Total Processed: {$results['processed']}");
        if ($isDryRun) {
            $this->line("Would Update: {$results['processed']}");
        } else {
            $this->line("Updated: {$results['updated']}");
        }
        $this->line("Errors: {$results['errors']}");

        if ($isDryRun) {
            $this->newLine();
            $this->warn('This was a DRY RUN. Run without --dry-run to apply changes.');
        } else {
            $this->newLine();
            $this->info('✓ All users have been updated with their initial crystal modifiers!');
        }

        return Command::SUCCESS;
    }
}
