<?php

/*
 * Webtech-solutions 2025, All rights reserved.
 */

namespace App\Filament\Resources\ScheduledEmailResource\Pages;

use App\Filament\Admin\Resources\ScheduledEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScheduledEmail extends EditRecord
{
    protected static string $resource = ScheduledEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
