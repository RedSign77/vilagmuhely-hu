<?php

namespace Webtechsolutions\ContentEngine\Filament\Resources\ContentCategoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Webtechsolutions\ContentEngine\Filament\Resources\ContentCategoryResource;

class ListContentCategories extends ListRecords
{
    protected static string $resource = ContentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
