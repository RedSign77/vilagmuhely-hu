<?php

namespace Webtechsolutions\QueueManager\Listeners;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\DB;
use Webtechsolutions\QueueManager\Models\CompletedJob;

class MoveCompletedJobToHistory
{
    protected static array $processingJobs = [];

    /**
     * Handle the event.
     */
    public function handle(JobProcessed $event): void
    {
        try {
            $connectionName = $event->connectionName;
            $job = $event->job;

            // Only track database queue jobs
            if ($connectionName !== 'database') {
                return;
            }

            $jobId = $job->getJobId();

            // Check if we have stored job data from processing
            if (isset(self::$processingJobs[$jobId])) {
                $rawJob = self::$processingJobs[$jobId];

                // Create completed job record
                CompletedJob::create([
                    'queue' => $rawJob->queue,
                    'payload' => $rawJob->payload,
                    'attempts' => $rawJob->attempts,
                    'reserved_at' => $rawJob->reserved_at,
                    'available_at' => $rawJob->available_at,
                    'created_at' => $rawJob->created_at,
                    'completed_at' => now(),
                ]);

                // Clean up stored data
                unset(self::$processingJobs[$jobId]);
            }
        } catch (\Exception $e) {
            // Silently fail - don't break the queue process
            \Log::error('Failed to move completed job to history: ' . $e->getMessage());
        }
    }

    /**
     * Store job data before processing
     */
    public static function storeJobData(int $jobId, object $rawJob): void
    {
        self::$processingJobs[$jobId] = $rawJob;
    }
}
