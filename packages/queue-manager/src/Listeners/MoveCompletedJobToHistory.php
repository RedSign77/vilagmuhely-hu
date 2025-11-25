<?php

namespace Webtechsolutions\QueueManager\Listeners;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\DB;
use Webtechsolutions\QueueManager\Models\CompletedJob;

class MoveCompletedJobToHistory
{
    /**
     * Handle the event.
     */
    public function handle(JobProcessed $event): void
    {
        try {
            // Get the job data before it's deleted
            $connectionName = $event->connectionName;
            $job = $event->job;

            // Only track database queue jobs
            if ($connectionName !== 'database') {
                return;
            }

            // Get the raw job from the database before it's deleted
            $rawJob = DB::connection()->table('jobs')
                ->where('id', $job->getJobId())
                ->first();

            // If job still exists, move it to completed_jobs
            if ($rawJob) {
                CompletedJob::create([
                    'queue' => $rawJob->queue,
                    'payload' => $rawJob->payload,
                    'attempts' => $rawJob->attempts,
                    'reserved_at' => $rawJob->reserved_at,
                    'available_at' => $rawJob->available_at,
                    'created_at' => $rawJob->created_at,
                    'completed_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail - don't break the queue process
            \Log::error('Failed to move completed job to history: ' . $e->getMessage());
        }
    }
}
