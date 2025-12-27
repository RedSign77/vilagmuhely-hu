<?php

namespace App\Filament\Admin\Resources\ExpeditionEnrollmentResource\Pages;

use App\Filament\Admin\Resources\ExpeditionEnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpeditionEnrollment extends EditRecord
{
    protected static string $resource = ExpeditionEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
