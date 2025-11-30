<?php

namespace Webtechsolutions\Mailer\Console\Commands;

use Illuminate\Console\Command;
use Webtechsolutions\Mailer\Models\SentEmail;

class CleanupSentEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailer:cleanup-sent {--days=7 : Number of days to keep sent emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete sent emails older than the specified number of days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');

        $this->info("Cleaning up sent emails older than {$days} days...");

        $count = SentEmail::deleteOlderThan($days);

        if ($count > 0) {
            $this->info("Successfully deleted {$count} sent email(s).");
        } else {
            $this->info('No sent emails to clean up.');
        }

        return Command::SUCCESS;
    }
}
