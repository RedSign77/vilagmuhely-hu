<?php

/*
 * Webtech-solutions 2025, All rights reserved.
 */

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class TemplateEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $processedSubject;
    public string $processedBody;

    /**
     * Create a new message instance.
     *
     * @param EmailTemplate $template The email template to use
     * @param array $variables Key-value pairs for variable replacement (e.g., ['name' => 'John'])
     */
    public function __construct(
        public EmailTemplate $template,
        public array $variables = []
    ) {
        // Process template with variables
        $processed = $this->template->replaceVariables($this->variables);
        $this->processedSubject = $processed['subject'];
        $this->processedBody = Str::markdown($processed['body']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->processedSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.template',
            with: [
                'content' => $this->processedBody,
            ],
        );
    }

    /**
     * Get the message theme
     */
    protected function theme(): ?string
    {
        return 'vilagmuhely';
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
