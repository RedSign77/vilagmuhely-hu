<?php

namespace App\Filament\Admin\Resources\ExpeditionResource\Pages;

use App\Filament\Admin\Resources\ExpeditionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpedition extends EditRecord
{
    protected static string $resource = ExpeditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
