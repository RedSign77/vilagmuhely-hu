<?php

namespace App\Filament\Admin\Resources\InvitationResource\Pages;

use App\Filament\Admin\Resources\InvitationResource;
use App\Models\Invitation;
use App\Notifications\InvitationNotification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class CreateInvitation extends CreateRecord
{
    protected static string $resource = InvitationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Check for existing invitations with the same email
        $existingInvitation = Invitation::where('email', $data['email'])->first();

        if ($existingInvitation) {
            // If the existing invitation is accepted, prevent creating a new one
            if ($existingInvitation->status === 'accepted') {
                throw ValidationException::withMessages([
                    'email' => 'This email address has already accepted an invitation. Cannot send a new invitation.',
                ]);
            }

            // If the existing invitation is pending/expired/cancelled, cancel it
            if ($existingInvitation->status !== 'accepted') {
                $existingInvitation->update(['status' => 'cancelled']);

                FilamentNotification::make()
                    ->title('Previous invitation cancelled')
                    ->body("A previous invitation to {$data['email']} was cancelled and a new one will be sent.")
                    ->info()
                    ->send();
            }
        }

        $data['token'] = Invitation::generateToken();
        $data['expires_at'] = now()->addHours(config('invitations.expires_in_hours', 72));
        $data['invited_by_user_id'] = auth()->id();
        $data['status'] = 'pending';

        return $data;
    }

    protected function afterCreate(): void
    {
        // Send invitation email
        Notification::route('mail', $this->record->email)
            ->notify(new InvitationNotification($this->record));
    }
}
