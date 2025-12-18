<?php

namespace App\Filament\Admin\Pages;

use App\Models\Invitation;
use App\Notifications\InvitationNotification;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use Livewire\Component;

class InviteUserAction extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function inviteAction(): Action
    {
        return Action::make('invite')
            ->label('')
            ->icon('heroicon-o-user-plus')
            ->tooltip('Invite User')
            ->color('primary')
            ->modalHeading('Invite User')
            ->modalDescription('Send an invitation to join Világműhely')
            ->modalWidth('md')
            ->form([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('John Doe'),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->rule('unique:invitations,email,NULL,id,status,pending')
                    ->maxLength(255)
                    ->placeholder('john@example.com')
                    ->helperText('User will receive an invitation link'),

                Textarea::make('message')
                    ->label('Personal Message (Optional)')
                    ->rows(3)
                    ->maxLength(500)
                    ->placeholder('Add a personal message to the invitation...'),
            ])
            ->action(function (array $data) {
                $invitation = Invitation::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'message' => $data['message'] ?? null,
                    'invited_by_user_id' => auth()->id(),
                    'token' => Invitation::generateToken(),
                    'expires_at' => now()->addHours(config('invitations.expires_in_hours', 72)),
                    'status' => 'pending',
                ]);

                LaravelNotification::route('mail', $invitation->email)
                    ->notify(new InvitationNotification($invitation));

                Notification::make()
                    ->title('Invitation sent!')
                    ->body('An invitation has been sent to '.$data['email'])
                    ->success()
                    ->send();
            });
    }

    public function render()
    {
        return view('filament.topbar.invite-user-action');
    }
}
