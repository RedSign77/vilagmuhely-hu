<?php

namespace Webtechsolutions\ContentEngine\Console\Commands;

use Illuminate\Console\Command;
use Webtechsolutions\ContentEngine\Jobs\RecalculateCrystalMetricsJob;
use Webtechsolutions\ContentEngine\Models\CrystalActivityQueue;

class ProcessCrystalUpdatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crystal:process-updates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process queued crystal metric updates (batch recalculation)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing crystal updates...');

        // Get distinct user IDs with unprocessed activities
        $userIds = CrystalActivityQueue::getUnprocessedUserIds();

        if (empty($userIds)) {
            $this->info('No pending crystal updates to process.');
            return Command::SUCCESS;
        }

        $this->info("Found {count($userIds)} user(s) with pending updates.");

        // Dispatch recalculation jobs for each user
        foreach ($userIds as $userId) {
            RecalculateCrystalMetricsJob::dispatch($userId);
        }

        $this->info("Queued " . count($userIds) . " crystal recalculation job(s).");

        return Command::SUCCESS;
    }
}
