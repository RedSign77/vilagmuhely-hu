<?php

namespace Webtechsolutions\ContentEngine\Filament\Resources\ContentCategoryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webtechsolutions\ContentEngine\Filament\Resources\ContentCategoryResource;

class CreateContentCategory extends CreateRecord
{
    protected static string $resource = ContentCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
