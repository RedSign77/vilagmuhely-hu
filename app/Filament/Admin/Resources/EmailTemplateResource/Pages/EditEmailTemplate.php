<?php

/*
 * Webtech-solutions 2025, All rights reserved.
 */

namespace App\Filament\Admin\Resources\EmailTemplateResource\Pages;

use App\Filament\Admin\Resources\EmailTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class EditEmailTemplate extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Preview Email')
                ->icon('heroicon-o-eye')
                ->modalHeading(fn () => 'Preview: ' . $this->record->subject)
                ->modalContent(function () {
                    $html = Str::markdown($this->record->body);
                    return new HtmlString(view('emails.template-preview', [
                        'content' => $html,
                    ])->render());
                })
                ->modalWidth('4xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->color('info'),
            Actions\DeleteAction::make(),
        ];
    }
}
