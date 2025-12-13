<?php

namespace Webtechsolutions\QueueManager\Filament\Resources\CompletedJobResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Webtechsolutions\QueueManager\Filament\Resources\CompletedJobResource;
use Webtechsolutions\QueueManager\Models\CompletedJob;

class ListCompletedJobs extends ListRecords
{
    protected static string $resource = CompletedJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('clear_old')
                ->label('Clear Old Jobs (7+ days)')
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Clear Old Completed Jobs')
                ->modalDescription('This will remove all completed jobs older than 7 days. This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, clear old jobs')
                ->action(function () {
                    return CompletedJob::deleteOlderThan(7);
                })
                ->successNotification(
                    fn ($data) => \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Old jobs cleared')
                        ->body('Completed jobs older than 7 days have been removed.')
                )
                ->visible(fn () => CompletedJob::olderThanDays(7)->exists()),

            Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->dispatch('$refresh')),
        ];
    }

    public function getTitle(): string
    {
        $total = CompletedJob::count();
        $today = CompletedJob::whereDate('completed_at', today())->count();

        return "Completed Jobs ({$total} total, {$today} today)";
    }
}
