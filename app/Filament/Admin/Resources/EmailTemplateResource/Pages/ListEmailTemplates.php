<?php

/*
 * Webtech-solutions 2025, All rights reserved.
 */

namespace App\Filament\Admin\Resources\EmailTemplateResource\Pages;

use App\Filament\Admin\Resources\EmailTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
