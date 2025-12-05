<?php

namespace Webtechsolutions\ContentEngine\Filament\Resources\ContentTagResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Webtechsolutions\ContentEngine\Filament\Resources\ContentTagResource;

class ListContentTags extends ListRecords
{
    protected static string $resource = ContentTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
