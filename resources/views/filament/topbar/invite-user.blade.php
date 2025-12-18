<div class="flex items-center">
    {{
        \Filament\Actions\Action::make('invite_user')
            ->label('')
            ->icon('heroicon-o-user-plus')
            ->tooltip('Invite User')
            ->color('primary')
            ->modalHeading('Invite User')
            ->modalDescription('Send an invitation to join Világműhely')
            ->modalWidth('md')
            ->form([
                \Filament\Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('John Doe'),

                \Filament\Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique('invitations', 'email', function ($query) {
                        return $query->where('status', 'pending');
                    })
                    ->maxLength(255)
                    ->placeholder('john@example.com')
                    ->helperText('User will receive an invitation link'),

                \Filament\Forms\Components\Textarea::make('message')
                    ->label('Personal Message (Optional)')
                    ->rows(3)
                    ->maxLength(500)
                    ->placeholder('Add a personal message to the invitation...'),
            ])
            ->action(function (array $data) {
                $invitation = \App\Models\Invitation::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'message' => $data['message'] ?? null,
                    'invited_by_user_id' => auth()->id(),
                    'token' => \App\Models\Invitation::generateToken(),
                    'expires_at' => now()->addHours(config('invitations.expires_in_hours', 72)),
                    'status' => 'pending',
                ]);

                \Illuminate\Support\Facades\Notification::route('mail', $invitation->email)
                    ->notify(new \App\Notifications\InvitationNotification($invitation));

                \Filament\Notifications\Notification::make()
                    ->title('Invitation sent!')
                    ->body('An invitation has been sent to ' . $data['email'])
                    ->success()
                    ->send();
            })
    }}
</div>
