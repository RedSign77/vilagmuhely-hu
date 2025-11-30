<?php

namespace Webtechsolutions\UserManager\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Webtechsolutions\UserManager\Filament\Resources\UserResource;
use Webtechsolutions\UserManager\Observers\UserObserver;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Hook to track role changes before saving
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Store current role IDs before save
        $this->oldRoleIds = $this->record->roles()->pluck('roles.id')->toArray();

        return $data;
    }

    /**
     * Hook after save to log role changes
     */
    protected function afterSave(): void
    {
        // Get new role IDs after save
        $newRoleIds = $this->record->roles()->pluck('roles.id')->toArray();

        // Log role changes if any
        if (isset($this->oldRoleIds)) {
            UserObserver::logRoleChange($this->record, $this->oldRoleIds, $newRoleIds);
        }
    }
}
