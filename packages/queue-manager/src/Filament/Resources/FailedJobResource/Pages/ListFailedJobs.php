<?php

namespace Webtechsolutions\QueueManager\Filament\Resources\FailedJobResource\Pages;

use Webtechsolutions\QueueManager\Filament\Resources\FailedJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Webtechsolutions\QueueManager\Models\FailedJob;
use Illuminate\Support\Facades\Artisan;

class ListFailedJobs extends ListRecords
{
    protected static string $resource = FailedJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('retry_all')
                ->label('Retry All')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Retry All Failed Jobs')
                ->modalDescription('Are you sure you want to retry ALL failed jobs?')
                ->modalSubmitActionLabel('Yes, retry all')
                ->action(function () {
                    Artisan::call('queue:retry', ['id' => ['all']]);
                    FailedJob::query()->delete();
                })
                ->successNotification(
                    fn () => \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('All jobs retried')
                        ->body('All failed jobs have been pushed back to the queue.')
                )
                ->visible(fn () => FailedJob::count() > 0),

            Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->dispatch('$refresh')),
        ];
    }

    public function getTitle(): string
    {
        $total = FailedJob::count();
        return "Failed Jobs ({$total} total)";
    }
}
