<?php

namespace Webtechsolutions\ContentEngine\Filament\Resources\ContentResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webtechsolutions\ContentEngine\Filament\Resources\ContentResource;

class CreateContent extends CreateRecord
{
    protected static string $resource = ContentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
