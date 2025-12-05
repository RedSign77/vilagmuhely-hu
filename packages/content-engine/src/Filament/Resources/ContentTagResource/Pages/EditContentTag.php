<?php

namespace Webtechsolutions\ContentEngine\Filament\Resources\ContentTagResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Webtechsolutions\ContentEngine\Filament\Resources\ContentTagResource;

class EditContentTag extends EditRecord
{
    protected static string $resource = ContentTagResource::class;

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
