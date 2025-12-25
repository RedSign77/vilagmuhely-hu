<?php

/*
 * Webtech-solutions 2025, All rights reserved.
 */

namespace App\Filament\Resources\ScheduledEmailResource\Pages;

use App\Filament\Admin\Resources\ScheduledEmailResource;
use App\Models\EmailDispatchLog;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScheduledEmails extends ListRecords
{
    protected static string $resource = ScheduledEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('clear_dispatch_logs')
                ->label('Clear Dispatch Logs')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Clear All Email Dispatch Logs')
                ->modalDescription('This will delete all email dispatch history, allowing scheduled emails to be re-sent to recipients. Use this for testing purposes only.')
                ->modalSubmitActionLabel('Clear All Logs')
                ->action(function () {
                    $count = EmailDispatchLog::count();
                    EmailDispatchLog::truncate();

                    \Filament\Notifications\Notification::make()
                        ->title('Dispatch Logs Cleared')
                        ->body("{$count} dispatch log(s) deleted. Deduplication reset.")
                        ->success()
                        ->send();
                })
                ->visible(fn () => EmailDispatchLog::count() > 0),
            Actions\CreateAction::make(),
        ];
    }
}
