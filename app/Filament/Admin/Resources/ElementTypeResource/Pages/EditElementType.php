<?php

namespace App\Filament\Admin\Resources\ElementTypeResource\Pages;

use App\Filament\Admin\Resources\ElementTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditElementType extends EditRecord
{
    protected static string $resource = ElementTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
