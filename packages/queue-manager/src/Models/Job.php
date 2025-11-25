<?php

namespace Webtechsolutions\QueueManager\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Job extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'jobs';

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
        'queue',
        'payload',
        'attempts',
        'reserved_at',
        'available_at',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'attempts' => 'integer',
        'reserved_at' => 'integer',
        'available_at' => 'integer',
        'created_at' => 'integer',
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
     * Check if the job is currently being processed.
     */
    public function isReserved(): bool
    {
        return !is_null($this->reserved_at) && $this->reserved_at > 0;
    }

    /**
     * Get the status of the job.
     */
    public function getStatusAttribute(): string
    {
        return $this->isReserved() ? 'Processing' : 'Pending';
    }

    /**
     * Get the created at timestamp as a Carbon instance.
     */
    public function getCreatedAtCarbonAttribute(): Carbon
    {
        return Carbon::createFromTimestamp($this->created_at);
    }

    /**
     * Get the available at timestamp as a Carbon instance.
     */
    public function getAvailableAtCarbonAttribute(): Carbon
    {
        return Carbon::createFromTimestamp($this->available_at);
    }

    /**
     * Get the reserved at timestamp as a Carbon instance.
     */
    public function getReservedAtCarbonAttribute(): ?Carbon
    {
        return $this->reserved_at ? Carbon::createFromTimestamp($this->reserved_at) : null;
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
     * Scope a query to only include pending jobs.
     */
    public function scopePending($query)
    {
        return $query->whereNull('reserved_at')->orWhere('reserved_at', 0);
    }

    /**
     * Scope a query to only include processing jobs.
     */
    public function scopeProcessing($query)
    {
        return $query->whereNotNull('reserved_at')->where('reserved_at', '>', 0);
    }

    /**
     * Scope a query to filter by queue name.
     */
    public function scopeOnQueue($query, string $queue)
    {
        return $query->where('queue', $queue);
    }
}
