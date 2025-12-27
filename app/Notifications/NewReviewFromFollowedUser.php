<?php

namespace App\Notifications;

use App\Models\ContentReview;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Webtechsolutions\ContentEngine\Models\Content;

class NewReviewFromFollowedUser extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ContentReview $review,
        public Content $content,
        public User $reviewer
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->prefersNotification('email_on_new_review')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'new_review',
            'reviewer_id' => $this->reviewer->id,
            'reviewer_name' => $this->reviewer->getDisplayName(),
            'reviewer_username' => $this->reviewer->username,
            'review_id' => $this->review->id,
            'review_title' => $this->review->title,
            'review_excerpt' => Str::limit($this->review->review_text, 100),
            'content_id' => $this->content->id,
            'content_title' => $this->content->title,
            'url' => route('library.index') . '?highlight=' . $this->content->id . '#review-' . $this->review->id,
            'icon' => '💬',
            'message' => "{$this->reviewer->getDisplayName()} reviewed: {$this->content->title}",
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->reviewer->getDisplayName()} posted a review")
            ->greeting("New review from a Crystal Master you follow!")
            ->line("{$this->reviewer->getDisplayName()} reviewed **{$this->content->title}**")
            ->line("*{$this->review->title}*")
            ->line(Str::limit($this->review->review_text, 150))
            ->action('Read Full Review', route('library.index') . '?highlight=' . $this->content->id . '#review-' . $this->review->id)
            ->line('Happy adventuring!');
    }
}
