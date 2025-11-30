<?php

namespace Webtechsolutions\QueueManager\Listeners;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\DB;

class CaptureJobBeforeProcessing
{
    /**
     * Handle the event.
     */
    public function handle(JobProcessing $event): void
    {
        try {
            $connectionName = $event->connectionName;
            $job = $event->job;

            // Only track database queue jobs
            if ($connectionName !== 'database') {
                return;
            }

            $jobId = $job->getJobId();

            // Get the raw job from the database before it's processed
            $rawJob = DB::connection()->table('jobs')
                ->where('id', $jobId)
                ->first();

            // Store the job data for later use
            if ($rawJob) {
                MoveCompletedJobToHistory::storeJobData($jobId, $rawJob);
            }
        } catch (\Exception $e) {
            // Silently fail - don't break the queue process
            \Log::error('Failed to capture job before processing: ' . $e->getMessage());
        }
    }
}
