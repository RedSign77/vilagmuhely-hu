<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationNotification extends Notification
{
    use Queueable;

    public function __construct(public Invitation $invitation)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = route('invitations.accept', $this->invitation->token);

        return (new MailMessage)
            ->subject('You have been invited to Világműhely')
            ->greeting('Hello '.$this->invitation->name.'!')
            ->line($this->invitation->invitedBy->name.' has invited you to join Világműhely.')
            ->when($this->invitation->message, fn ($mail) => $mail->line('Personal message: "'.$this->invitation->message.'"')
            )
            ->action('Accept Invitation', $url)
            ->line('This invitation will expire '.$this->invitation->expires_at->diffForHumans().'.');
    }
}
