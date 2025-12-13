<?php

namespace App\Filament\Admin\Resources\ElementTypeResource\Pages;

use App\Filament\Admin\Resources\ElementTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListElementTypes extends ListRecords
{
    protected static string $resource = ElementTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
