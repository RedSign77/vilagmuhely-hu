<?php

namespace App\Filament\Admin\Resources\ExpeditionEnrollmentResource\Pages;

use App\Filament\Admin\Resources\ExpeditionEnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpeditionEnrollments extends ListRecords
{
    protected static string $resource = ExpeditionEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
