<?php

/*
 * Webtech-solutions 2025, All rights reserved.
 */

namespace App\Console\Commands;

use App\Mail\TemplateEmail;
use App\Models\EmailDispatchLog;
use App\Models\Order;
use App\Models\ScheduledEmail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ProcessScheduledEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:process-scheduled {--id= : Process only specific scheduled email ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and dispatch scheduled emails based on cron expressions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = ScheduledEmail::query()->where('is_enabled', true);

        if ($this->option('id')) {
            $query->where('id', $this->option('id'));
        } else {
            // Only process if due
            $query->where(function ($q) {
                $q->whereNull('next_run_at')
                  ->orWhere('next_run_at', '<=', now());
            });
        }

        $scheduledEmails = $query->get();

        if ($scheduledEmails->isEmpty()) {
            $this->info('No scheduled emails to process.');
            return self::SUCCESS;
        }

        $this->info("Processing {$scheduledEmails->count()} scheduled email(s)...");

        foreach ($scheduledEmails as $scheduledEmail) {
            $this->processScheduledEmail($scheduledEmail);
        }

        $this->info('✓ All scheduled emails processed');
        return self::SUCCESS;
    }

    /**
     * Process a single scheduled email
     */
    protected function processScheduledEmail(ScheduledEmail $scheduledEmail): void
    {
        $this->line("\nProcessing: {$scheduledEmail->name}");

        $recipients = $this->getRecipients($scheduledEmail);

        if ($recipients->isEmpty()) {
            $this->warn('  No recipients found');
            $scheduledEmail->update([
                'last_run_at' => now(),
            ]);
            $scheduledEmail->updateNextRunTime();
            return;
        }

        $this->info("  Found {$recipients->count()} recipient(s)");

        $sent = 0;
        $skipped = 0;

        foreach ($recipients as $recipient) {
            $sourceRecordId = $scheduledEmail->data_source === 'orders'
                ? $recipient->id
                : $recipient->id;

            $userId = $scheduledEmail->data_source === 'orders'
                ? $recipient->buyer_id
                : $recipient->id;

            // Check deduplication
            if (EmailDispatchLog::wasDispatched(
                $scheduledEmail->id,
                $scheduledEmail->email_template_id,
                $sourceRecordId,
                $userId
            )) {
                $skipped++;
                continue;
            }

            try {
                $variables = $this->buildVariables($scheduledEmail, $recipient);

                $email = $scheduledEmail->data_source === 'orders'
                    ? $recipient->buyer->email
                    : $recipient->email;

                Mail::to($email)->send(
                    new TemplateEmail($scheduledEmail->emailTemplate, $variables)
                );

                // Log successful dispatch
                EmailDispatchLog::create([
                    'scheduled_email_id' => $scheduledEmail->id,
                    'email_template_id' => $scheduledEmail->email_template_id,
                    'recipient_user_id' => $userId,
                    'data_source' => $scheduledEmail->data_source,
                    'source_record_id' => $sourceRecordId,
                    'recipient_email' => $email,
                    'status' => 'sent',
                ]);

                $sent++;
            } catch (\Exception $e) {
                // Log failed dispatch
                EmailDispatchLog::create([
                    'scheduled_email_id' => $scheduledEmail->id,
                    'email_template_id' => $scheduledEmail->email_template_id,
                    'recipient_user_id' => $userId,
                    'data_source' => $scheduledEmail->data_source,
                    'source_record_id' => $sourceRecordId,
                    'recipient_email' => $email ?? 'unknown',
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

                $this->error("  Failed to send to {$email}: {$e->getMessage()}");
            }
        }

        // Update execution statistics
        $scheduledEmail->update([
            'last_run_at' => now(),
            'total_sent' => $scheduledEmail->total_sent + $sent,
        ]);

        $scheduledEmail->updateNextRunTime();

        $this->info("  Sent: {$sent}, Skipped (already sent): {$skipped}");
    }

    /**
     * Get recipients based on data source and filters
     */
    protected function getRecipients(ScheduledEmail $scheduledEmail)
    {
        if ($scheduledEmail->data_source === 'users') {
            return $this->getUserRecipients($scheduledEmail);
        }

        return $this->getOrderRecipients($scheduledEmail);
    }

    /**
     * Get user recipients based on recipient type
     */
    protected function getUserRecipients(ScheduledEmail $scheduledEmail)
    {
        $query = User::query();

        switch ($scheduledEmail->recipient_type) {
            case 'all':
                // All users
                break;

            case 'roles':
                if ($scheduledEmail->recipient_roles) {
                    $query->where(function ($q) use ($scheduledEmail) {
                        foreach ($scheduledEmail->recipient_roles as $role) {
                            if ($role === 'supervisor') {
                                $q->orWhere('supervisor', true);
                            } elseif ($role === 'user') {
                                $q->orWhere('supervisor', false);
                            }
                        }
                    });
                }
                break;

            case 'individual':
                if ($scheduledEmail->recipient_users) {
                    $query->whereIn('id', $scheduledEmail->recipient_users);
                }
                break;
        }

        return $query->get();
    }

    /**
     * Get order recipients based on status and lookback window
     */
    protected function getOrderRecipients(ScheduledEmail $scheduledEmail)
    {
        $query = Order::query()->with('buyer');

        if ($scheduledEmail->order_statuses) {
            $query->whereIn('order_status', $scheduledEmail->order_statuses);
        }

        if ($scheduledEmail->lookback_hours) {
            $query->where('updated_at', '>=', now()->subHours($scheduledEmail->lookback_hours));
        }

        return $query->get();
    }

    /**
     * Build template variables from record data
     */
    protected function buildVariables(ScheduledEmail $scheduledEmail, $record): array
    {
        if ($scheduledEmail->data_source === 'users') {
            return [
                'name' => $record->name,
                'email' => $record->email,
            ];
        }

        // For orders
        return [
            'name' => $record->buyer->name,
            'email' => $record->buyer->email,
            'order_number' => $record->order_number,
            'order_status' => ucfirst($record->order_status),
            'total_amount' => number_format($record->total_amount, 2),
        ];
    }
}
