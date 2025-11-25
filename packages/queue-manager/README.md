# Queue Manager Package

A Laravel package for managing queued jobs with a Filament admin interface. Monitor pending jobs, handle failures, and track queue statistics.

## Features

- **Pending Jobs Management**
  - View all queued jobs
  - Monitor job status (Pending/Processing)
  - View job details in slide-over modal
  - Run jobs immediately
  - Terminate jobs
  - Bulk operations support

- **Failed Jobs Management**
  - View all failed jobs
  - See exception details and stack traces
  - Retry individual or multiple failed jobs
  - Clear failed jobs
  - Filter by queue, connection, or date range

- **Completed Jobs Tracking**
  - Automatically track all successfully completed jobs
  - View job execution time
  - See completion timestamps
  - Filter by queue and date range
  - Clear old completed jobs
  - **Automatic cleanup**: Jobs older than 7 days are automatically deleted daily at 2:00 AM

- **Filament Integration**: Beautiful admin interface with Filament 3.x
- **Configuration Menu**: Organized under "Configuration" navigation group
- **Real-time Updates**: Auto-refresh (Pending/Failed: 10s, Completed: 30s)
- **Search & Filter**: Find jobs quickly with comprehensive filters
- **Scheduled Cleanup**: Automatic deletion of old completed jobs via cron

## Installation

The package is already installed as a local package. It's configured in the root `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/queue-manager"
        }
    ],
    "require": {
        "vilagmuhely/queue-manager": "@dev"
    }
}
```

## Requirements

- PHP ^8.2
- Laravel ^12.0
- Filament ^3.3
- Queue driver set to `database` in `config/queue.php`

## Configuration

### Queue Driver

This package requires the `database` queue driver. Set in your `.env`:

```env
QUEUE_CONNECTION=database
```

### Database Tables

The package uses Laravel's standard queue tables:
- `jobs` - Pending and processing jobs
- `failed_jobs` - Failed jobs

Make sure you've run the migrations:

```bash
php artisan migrate
```

## Usage

### Accessing the Queue Manager

1. Navigate to your Filament admin panel (typically `/admin`)
2. Look for the "Configuration" navigation group
3. You'll find three sub-menus:
   - **Pending Jobs** - View and manage queued jobs
   - **Failed Jobs** - Handle failed jobs
   - **Completed Jobs** - View queue statistics

### Pending Jobs

**Table Features:**
- **ID**: Unique job identifier
- **Queue**: Queue name the job belongs to
- **Job Class**: The job class name
- **Attempts**: Number of times the job has been attempted
- **Status**: Pending or Processing
- **Created**: When the job was queued

**Actions:**
- **View**: Opens slide-over modal with full job details including payload
- **Run Now**: Makes the job available for immediate processing
- **Terminate**: Removes the job from the queue

**Bulk Actions:**
- Run multiple jobs immediately
- Terminate selected jobs

**Filters:**
- Filter by queue name
- Filter by status (Pending/Processing)

### Failed Jobs

**Table Features:**
- **ID**: Job identifier
- **UUID**: Unique identifier for the failed job
- **Queue**: Queue name
- **Job Class**: The job that failed
- **Exception**: Exception type that caused the failure
- **Failed At**: Timestamp when job failed

**Actions:**
- **View**: Opens slide-over modal showing:
  - Full job details
  - Exception message
  - Complete stack trace
  - Job payload
- **Retry**: Pushes the job back to the queue for retry
- **Clear**: Removes the failed job record

**Bulk Actions:**
- Retry selected failed jobs
- Clear selected failed jobs
- Clear all failed jobs (with confirmation)

**Header Actions:**
- **Retry All**: Retry all failed jobs at once

**Filters:**
- Filter by queue name
- Filter by connection
- Filter by failure date range

### Completed Jobs

The Completed Jobs page automatically tracks all successfully processed jobs.

**Table Features:**
- **ID**: Job identifier
- **Queue**: Queue name
- **Job Class**: The job that completed
- **Attempts**: Number of attempts before completion
- **Execution Time**: How long the job took to run
- **Completed**: When the job finished

**Actions:**
- **View**: Opens slide-over modal with full job details
- **Clear**: Removes the completed job record

**Bulk Actions:**
- Clear selected completed jobs
- Clear all completed jobs (with confirmation)

**Header Actions:**
- **Clear Old Jobs (7+ days)**: Manually clear jobs older than 7 days (only visible when old jobs exist)
- **Refresh**: Reload the table

**Filters:**
- Filter by queue name
- Filter by completion date range

**Automatic Cleanup:**
- Jobs older than 7 days are automatically deleted daily at 2:00 AM
- This runs via Laravel's scheduler
- Make sure `php artisan schedule:run` is set up in your cron

**How it Works:**
When a job completes successfully, it's automatically moved from the `jobs` table to the `completed_jobs` table via the `JobProcessed` event listener. This preserves the job history for analysis while keeping the active queue table clean.

## API Methods

### Job Model

```php
use Vilagmuhely\QueueManager\Models\Job;

// Get job class name
$job->job_class;

// Get job data
$job->job_data;

// Check if job is being processed
$job->isReserved();

// Get job status
$job->status; // 'Pending' or 'Processing'

// Scope queries
Job::pending()->get();
Job::processing()->get();
Job::onQueue('emails')->get();
```

### FailedJob Model

```php
use Vilagmuhely\QueueManager\Models\FailedJob;

// Get job class name
$failedJob->job_class;

// Get exception summary
$failedJob->exception_summary;

// Get exception message (without stack trace)
$failedJob->exception_message;

// Get exception class
$failedJob->exception_class;

// Retry a failed job
$failedJob->retry();

// Retry multiple jobs
FailedJob::retryMultiple(['uuid1', 'uuid2']);

// Clear all failed jobs
FailedJob::clearAll();

// Scope queries
FailedJob::onQueue('emails')->get();
FailedJob::onConnection('database')->get();
```

### CompletedJob Model

```php
use Vilagmuhely\QueueManager\Models\CompletedJob;

// Get job class name
$completedJob->job_class;

// Get job data
$completedJob->job_data;

// Get execution time in seconds
$completedJob->execution_time;

// Get created at as Carbon instance
$completedJob->created_at_carbon;

// Get completed at timestamp
$completedJob->completed_at; // Carbon instance

// Delete jobs older than X days
CompletedJob::deleteOlderThan(7); // Returns count of deleted jobs

// Scope queries
CompletedJob::onQueue('emails')->get();
CompletedJob::olderThanDays(7)->get();
```

## Artisan Commands

### Clean Up Completed Jobs

Manually clean up old completed jobs:

```bash
# Delete completed jobs older than 7 days (default)
php artisan queue:cleanup-completed

# Delete completed jobs older than 30 days
php artisan queue:cleanup-completed --days=30
```

This command is automatically scheduled to run daily at 2:00 AM for jobs older than 7 days.

## Scheduler Setup

The package includes an automatic cleanup task that runs daily. Make sure your Laravel scheduler is configured:

**Add to cron (production):**
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**Or use the scheduler in development:**
```bash
php artisan schedule:work
```

The scheduled task will run `queue:cleanup-completed --days=7` daily at 2:00 AM.

## Package Structure

```
packages/queue-manager/
├── composer.json
├── README.md
├── database/
│   └── migrations/
│       └── 2024_01_01_000000_create_completed_jobs_table.php
└── src/
    ├── Models/
    │   ├── Job.php
    │   ├── FailedJob.php
    │   └── CompletedJob.php
    ├── Listeners/
    │   └── MoveCompletedJobToHistory.php
    ├── Console/
    │   └── Commands/
    │       └── CleanupCompletedJobsCommand.php
    ├── Providers/
    │   └── QueueManagerServiceProvider.php
    └── Filament/
        └── Resources/
            ├── PendingJobResource.php
            ├── PendingJobResource/Pages/ListPendingJobs.php
            ├── FailedJobResource.php
            ├── FailedJobResource/Pages/ListFailedJobs.php
            ├── CompletedJobResource.php
            └── CompletedJobResource/Pages/ListCompletedJobs.php
```

## Customization

### Navigation

To change navigation settings, edit the resource classes:

```php
// Change navigation group
protected static ?string $navigationGroup = 'Your Group Name';

// Change navigation label
protected static ?string $navigationLabel = 'Your Label';

// Change navigation icon
protected static ?string $navigationIcon = 'heroicon-o-your-icon';

// Change navigation sort order
protected static ?int $navigationSort = 10;
```

### Polling Interval

The tables auto-refresh every 10 seconds by default. To change this, modify the `->poll()` setting in the resource table configuration:

```php
->poll('30s') // Refresh every 30 seconds
->poll('1m')  // Refresh every 1 minute
->poll(null)  // Disable auto-refresh
```

## Tips

1. **Monitor Failed Jobs**: Regularly check the Failed Jobs page to catch and fix issues
2. **Retry Strategically**: Before retrying failed jobs, fix the underlying issue
3. **Queue Names**: Use descriptive queue names to organize different types of jobs
4. **Job Cleanup**: Periodically clear old failed jobs that have been resolved
5. **Performance**: For high-volume queues, consider using Redis instead of database driver

## Troubleshooting

### Jobs not appearing in Pending Jobs

- Check that `QUEUE_CONNECTION=database` in your `.env`
- Verify the `jobs` table exists in your database
- Ensure you're dispatching jobs correctly

### Failed jobs not showing

- Check that the `failed_jobs` table exists
- Verify `QUEUE_FAILED_DRIVER=database-uuids` in your `.env`

### Retry not working

- Ensure the queue worker is running: `php artisan queue:work`
- Check application logs for errors

## License

MIT License
