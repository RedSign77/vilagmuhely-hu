<?php

namespace Webtechsolutions\QueueManager\Filament\Resources\PendingJobResource\Pages;

use Webtechsolutions\QueueManager\Filament\Resources\PendingJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Webtechsolutions\QueueManager\Models\Job;

class ListPendingJobs extends ListRecords
{
    protected static string $resource = PendingJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->dispatch('$refresh')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            //
        ];
    }

    public function getTitle(): string
    {
        $total = Job::count();
        $pending = Job::pending()->count();
        $processing = Job::processing()->count();

        return "Pending Jobs ({$total} total, {$pending} pending, {$processing} processing)";
    }
}
