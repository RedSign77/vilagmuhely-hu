<?php

namespace Webtechsolutions\Mailer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Webtechsolutions\Mailer\Mail\DynamicEmail;
use Webtechsolutions\Mailer\Models\SentEmail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $sentEmailId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sentEmail = SentEmail::findOrFail($this->sentEmailId);

        try {
            Mail::to($sentEmail->recipient_email)
                ->send(new DynamicEmail($sentEmail->subject, $sentEmail->body));

            $sentEmail->markAsSent();
        } catch (\Exception $e) {
            $sentEmail->markAsFailed($e->getMessage());
            throw $e;
        }
    }
}
