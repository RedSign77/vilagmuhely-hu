<?php

namespace App\Notifications;

use App\Models\Expedition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpeditionCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Expedition $expedition
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable): array
    {
        $rewards = $this->expedition->rewards;

        return [
            'type' => 'expedition_completed',
            'expedition_id' => $this->expedition->id,
            'expedition_title' => $this->expedition->title,
            'crystal_multiplier' => $rewards['crystal_multiplier'] ?? 2.0,
            'visual_effect' => $rewards['visual_effect'] ?? null,
            'url' => route('expeditions.show', $this->expedition->slug),
            'icon' => '🏆',
            'message' => "Congratulations! You completed the {$this->expedition->title} expedition!",
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $rewards = $this->expedition->rewards;
        $multiplier = $rewards['crystal_multiplier'] ?? 2.0;
        $effect = $rewards['visual_effect'] ?? null;

        return (new MailMessage)
            ->subject("Expedition Completed: {$this->expedition->title}")
            ->greeting("Congratulations, Crystal Master!")
            ->line("You've successfully completed **{$this->expedition->title}**!")
            ->line("**Rewards Earned:**")
            ->line("🔮 {$multiplier}x Crystal Growth Multiplier")
            ->when($effect, function ($mail) use ($effect) {
                return $mail->line("✨ {$effect} Visual Effect");
            })
            ->action('View Your Forge', route('forge.profile', $notifiable->username))
            ->line('Your crystal will reflect these achievements!')
            ->line('Happy adventuring!');
    }
}
