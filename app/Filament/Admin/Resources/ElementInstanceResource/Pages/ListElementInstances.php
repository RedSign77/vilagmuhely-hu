<?php

namespace App\Filament\Admin\Resources\ElementInstanceResource\Pages;

use App\Filament\Admin\Resources\ElementInstanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListElementInstances extends ListRecords
{
    protected static string $resource = ElementInstanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
