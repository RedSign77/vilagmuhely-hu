<?php

namespace App\Filament\Admin\Resources\InvitationResource\Pages;

use App\Filament\Admin\Resources\InvitationResource;
use App\Models\Invitation;
use App\Notifications\InvitationNotification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Notification;

class CreateInvitation extends CreateRecord
{
    protected static string $resource = InvitationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
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
