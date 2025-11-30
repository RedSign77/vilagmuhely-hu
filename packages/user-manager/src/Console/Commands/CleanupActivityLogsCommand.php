<?php

namespace Webtechsolutions\UserManager\Console\Commands;

use Illuminate\Console\Command;
use Webtechsolutions\UserManager\Models\UserActivityLog;

class CleanupActivityLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user-manager:cleanup-activity-logs {--days=90 : Number of days to keep activity logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete user activity logs older than the specified number of days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');

        $this->info("Cleaning up activity logs older than {$days} days...");

        $count = UserActivityLog::deleteOlderThan($days);

        if ($count > 0) {
            $this->info("Successfully deleted {$count} activity log(s).");
        } else {
            $this->info('No activity logs to clean up.');
        }

        return Command::SUCCESS;
    }
}
