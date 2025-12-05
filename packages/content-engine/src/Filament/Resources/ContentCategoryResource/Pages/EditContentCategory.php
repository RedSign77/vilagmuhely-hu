<?php

namespace Webtechsolutions\ContentEngine\Filament\Resources\ContentCategoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Webtechsolutions\ContentEngine\Filament\Resources\ContentCategoryResource;

class EditContentCategory extends EditRecord
{
    protected static string $resource = ContentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
