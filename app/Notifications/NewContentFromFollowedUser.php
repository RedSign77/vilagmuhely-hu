<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Webtechsolutions\ContentEngine\Models\Content;

class NewContentFromFollowedUser extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Content $content,
        public User $creator
    ) {}

    /**
     * Determine notification channels based on user preferences
     */
    public function via($notifiable): array
    {
        $channels = ['database']; // Always save to database

        if ($notifiable->prefersNotification('email_on_new_content')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get database notification data
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'new_content',
            'creator_id' => $this->creator->id,
            'creator_name' => $this->creator->anonymized_name,
            'creator_username' => $this->creator->username,
            'content_id' => $this->content->id,
            'content_title' => $this->content->title,
            'content_type' => $this->content->type,
            'content_excerpt' => $this->content->excerpt ?? '',
            'url' => route('library.index') . '?highlight=' . $this->content->id,
            'icon' => '📝',
            'message' => "{$this->creator->anonymized_name} published new content: {$this->content->title}",
        ];
    }

    /**
     * Get email notification
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->creator->anonymized_name} published new content")
            ->greeting("New from a Crystal Master you follow!")
            ->line("{$this->creator->anonymized_name} just published: **{$this->content->title}**")
            ->line($this->content->excerpt ?? $this->content->description ?? '')
            ->action('View Content', route('library.index') . '?highlight=' . $this->content->id)
            ->line('Happy adventuring!');
    }
}
