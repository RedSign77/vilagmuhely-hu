<?php

namespace Webtechsolutions\QueueManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class FailedJob extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'failed_jobs';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'connection',
        'queue',
        'payload',
        'exception',
        'failed_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'failed_at' => 'datetime',
    ];

    /**
     * Get the job class name from the payload.
     */
    public function getJobClassAttribute(): string
    {
        $payload = $this->unserializePayload();
        return $payload['displayName'] ?? 'Unknown Job';
    }

    /**
     * Get the job data from the payload.
     */
    public function getJobDataAttribute(): ?array
    {
        $payload = $this->unserializePayload();
        return $payload['data'] ?? null;
    }

    /**
     * Get a short summary of the exception.
     */
    public function getExceptionSummaryAttribute(): string
    {
        $lines = explode("\n", $this->exception);
        return $lines[0] ?? 'No exception message';
    }

    /**
     * Get the exception message only (without stack trace).
     */
    public function getExceptionMessageAttribute(): string
    {
        $exception = $this->exception;
        $firstLine = explode("\n", $exception)[0] ?? '';

        // Extract just the message part if it follows the pattern "ExceptionClass: message"
        if (preg_match('/^([^:]+):\s*(.+)$/', $firstLine, $matches)) {
            return $matches[2];
        }

        return $firstLine;
    }

    /**
     * Get the exception class name.
     */
    public function getExceptionClassAttribute(): string
    {
        $exception = $this->exception;
        $firstLine = explode("\n", $exception)[0] ?? '';

        // Extract exception class if it follows the pattern "ExceptionClass: message"
        if (preg_match('/^([^:]+):\s*(.+)$/', $firstLine, $matches)) {
            return $matches[1];
        }

        return 'Exception';
    }

    /**
     * Unserialize the job payload.
     */
    protected function unserializePayload(): array
    {
        try {
            $data = json_decode($this->payload, true);

            if (!$data) {
                return ['displayName' => 'Invalid Payload', 'data' => null];
            }

            return [
                'displayName' => $data['displayName'] ?? $data['data']['commandName'] ?? 'Unknown',
                'job' => $data['job'] ?? null,
                'data' => $data['data'] ?? null,
                'maxTries' => $data['maxTries'] ?? null,
                'timeout' => $data['timeout'] ?? null,
            ];
        } catch (\Exception $e) {
            return ['displayName' => 'Parse Error', 'data' => null];
        }
    }

    /**
     * Retry this failed job.
     */
    public function retry(): bool
    {
        try {
            Artisan::call('queue:retry', ['id' => [$this->uuid]]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Retry multiple failed jobs by UUID.
     */
    public static function retryMultiple(array $uuids): int
    {
        try {
            Artisan::call('queue:retry', ['id' => $uuids]);
            return count($uuids);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Clear all failed jobs.
     */
    public static function clearAll(): int
    {
        return static::query()->delete();
    }

    /**
     * Scope a query to filter by queue name.
     */
    public function scopeOnQueue($query, string $queue)
    {
        return $query->where('queue', $queue);
    }

    /**
     * Scope a query to filter by connection.
     */
    public function scopeOnConnection($query, string $connection)
    {
        return $query->where('connection', $connection);
    }
}
