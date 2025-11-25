<?php

namespace Webtechsolutions\QueueManager\Console\Commands;

use Illuminate\Console\Command;
use Webtechsolutions\QueueManager\Models\CompletedJob;

class CleanupCompletedJobsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:cleanup-completed {--days=7 : Number of days to keep completed jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete completed jobs older than the specified number of days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');

        $this->info("Cleaning up completed jobs older than {$days} days...");

        $count = CompletedJob::deleteOlderThan($days);

        if ($count > 0) {
            $this->info("Successfully deleted {$count} completed job(s).");
        } else {
            $this->info('No completed jobs to clean up.');
        }

        return Command::SUCCESS;
    }
}
