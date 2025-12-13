<?php

namespace App\Filament\Admin\Resources\ElementTypeResource\Pages;

use App\Filament\Admin\Resources\ElementTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewElementType extends ViewRecord
{
    protected static string $resource = ElementTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
