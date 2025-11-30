<?php

namespace Webtechsolutions\Mailer\Filament\Resources\EmailTemplateResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Webtechsolutions\Mailer\Filament\Resources\EmailTemplateResource;

class ListEmailTemplates extends ListRecords
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
