<?php

/*
 * Webtech-solutions 2025, All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailDispatchLog extends Model
{
    protected $fillable = [
        'scheduled_email_id',
        'email_template_id',
        'recipient_user_id',
        'data_source',
        'source_record_id',
        'recipient_email',
        'status',
        'error_message',
    ];

    /**
     * Get the scheduled email
     */
    public function scheduledEmail(): BelongsTo
    {
        return $this->belongsTo(ScheduledEmail::class);
    }

    /**
     * Get the email template
     */
    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    /**
     * Get the recipient user
     */
    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    /**
     * Check if email was already dispatched
     */
    public static function wasDispatched(int $scheduledEmailId, int $templateId, int $sourceRecordId, int $userId): bool
    {
        return self::where('scheduled_email_id', $scheduledEmailId)
            ->where('email_template_id', $templateId)
            ->where('source_record_id', $sourceRecordId)
            ->where('recipient_user_id', $userId)
            ->exists();
    }
}
