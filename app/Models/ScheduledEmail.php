<?php

/*
 * Webtech-solutions 2025, All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledEmail extends Model
{
    protected $fillable = [
        'name',
        'email_template_id',
        'cron_expression',
        'is_enabled',
        'data_source',
        'recipient_type',
        'recipient_roles',
        'recipient_users',
        'order_statuses',
        'lookback_hours',
        'variable_mapping',
        'last_run_at',
        'next_run_at',
        'total_sent',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'recipient_roles' => 'array',
        'recipient_users' => 'array',
        'order_statuses' => 'array',
        'variable_mapping' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    /**
     * Get the email template for this scheduled email
     */
    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    /**
     * Get human-readable cron expression
     */
    public function getHumanReadableCron(): string
    {
        if (! class_exists('\Cron\CronExpression')) {
            return $this->cron_expression;
        }

        try {
            $cron = new \Cron\CronExpression($this->cron_expression);
            return $cron->getNextRunDate()->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return 'Invalid cron expression';
        }
    }

    /**
     * Check if the scheduled email should run now
     */
    public function shouldRun(): bool
    {
        if (! $this->is_enabled) {
            return false;
        }

        if (! $this->next_run_at) {
            return true;
        }

        return now()->gte($this->next_run_at);
    }

    /**
     * Update next run time based on cron expression
     */
    public function updateNextRunTime(): void
    {
        if (! class_exists('\Cron\CronExpression')) {
            return;
        }

        try {
            $cron = new \Cron\CronExpression($this->cron_expression);
            $this->next_run_at = $cron->getNextRunDate();
            $this->save();
        } catch (\Exception $e) {
            // Invalid cron expression
        }
    }
}
