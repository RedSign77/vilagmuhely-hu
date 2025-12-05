<?php

namespace Webtechsolutions\ContentEngine\Filament\Resources\ContentTagResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webtechsolutions\ContentEngine\Filament\Resources\ContentTagResource;

class CreateContentTag extends CreateRecord
{
    protected static string $resource = ContentTagResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
