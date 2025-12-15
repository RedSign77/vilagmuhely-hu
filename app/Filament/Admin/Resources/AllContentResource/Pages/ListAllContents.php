<?php

namespace App\Filament\Admin\Resources\AllContentResource\Pages;

use App\Filament\Admin\Resources\AllContentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAllContents extends ListRecords
{
    protected static string $resource = AllContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
