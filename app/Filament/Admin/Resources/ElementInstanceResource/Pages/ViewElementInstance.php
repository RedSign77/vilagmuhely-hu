<?php

namespace App\Filament\Admin\Resources\ElementInstanceResource\Pages;

use App\Filament\Admin\Resources\ElementInstanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewElementInstance extends ViewRecord
{
    protected static string $resource = ElementInstanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
