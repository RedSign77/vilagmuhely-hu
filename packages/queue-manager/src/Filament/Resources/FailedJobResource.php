<?php

namespace Webtechsolutions\QueueManager\Filament\Resources;

use Webtechsolutions\QueueManager\Filament\Resources\FailedJobResource\Pages;
use Webtechsolutions\QueueManager\Models\FailedJob;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;

class FailedJobResource extends Resource
{
    protected static ?string $model = FailedJob::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Failed Jobs';

    protected static ?int $navigationSort = 21;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        return $count > 0 ? 'danger' : 'gray';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->copyable()
                    ->limit(13)
                    ->tooltip(fn ($record) => $record->uuid)
                    ->searchable(),

                Tables\Columns\TextColumn::make('queue')
                    ->label('Queue')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('job_class')
                    ->label('Job Class')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->job_class),

                Tables\Columns\TextColumn::make('exception_class')
                    ->label('Exception')
                    ->badge()
                    ->color('danger')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->exception_message),

                Tables\Columns\TextColumn::make('failed_at')
                    ->label('Failed At')
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('queue')
                    ->label('Queue')
                    ->options(function () {
                        return FailedJob::query()
                            ->select('queue')
                            ->distinct()
                            ->pluck('queue', 'queue')
                            ->toArray();
                    }),

                Tables\Filters\SelectFilter::make('connection')
                    ->label('Connection')
                    ->options(function () {
                        return FailedJob::query()
                            ->select('connection')
                            ->distinct()
                            ->pluck('connection', 'connection')
                            ->toArray();
                    }),

                Tables\Filters\Filter::make('failed_at')
                    ->form([
                        Forms\Components\DatePicker::make('failed_from')
                            ->label('Failed From'),
                        Forms\Components\DatePicker::make('failed_until')
                            ->label('Failed Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['failed_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('failed_at', '>=', $date),
                            )
                            ->when(
                                $data['failed_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('failed_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver()
                    ->infolist(fn (Infolist $infolist) => static::getInfolist($infolist)),

                Tables\Actions\Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (FailedJob $record) {
                        $success = $record->retry();
                        if ($success) {
                            $record->delete();
                        }
                        return $success;
                    })
                    ->successNotification(
                        fn () => \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Job retried')
                            ->body('The job has been pushed back to the queue.')
                    )
                    ->failureNotification(
                        fn () => \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('Retry failed')
                            ->body('Could not retry the job.')
                    ),

                Tables\Actions\DeleteAction::make()
                    ->label('Clear')
                    ->successNotification(
                        fn () => \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Job cleared')
                            ->body('The failed job has been removed.')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('retry')
                        ->label('Retry Selected')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $uuids = $records->pluck('uuid')->toArray();
                            $count = FailedJob::retryMultiple($uuids);

                            if ($count > 0) {
                                FailedJob::whereIn('uuid', $uuids)->delete();
                            }

                            return $count;
                        })
                        ->successNotification(
                            fn () => \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Jobs retried')
                                ->body('The selected jobs have been pushed back to the queue.')
                        ),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Clear Selected')
                        ->successNotification(
                            fn () => \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Jobs cleared')
                                ->body('The selected failed jobs have been removed.')
                        ),

                    Tables\Actions\BulkAction::make('clear_all')
                        ->label('Clear All')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Clear All Failed Jobs')
                        ->modalDescription('Are you sure you want to clear ALL failed jobs? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, clear all')
                        ->action(function () {
                            return FailedJob::clearAll();
                        })
                        ->successNotification(
                            fn ($data) => \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('All jobs cleared')
                                ->body('All failed jobs have been removed.')
                        )
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('failed_at', 'desc')
            ->poll('10s');
    }

    public static function getInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Failed Job Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('Job ID'),

                        Infolists\Components\TextEntry::make('uuid')
                            ->label('UUID')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('connection')
                            ->label('Connection')
                            ->badge()
                            ->color('gray'),

                        Infolists\Components\TextEntry::make('queue')
                            ->label('Queue')
                            ->badge()
                            ->color('gray'),

                        Infolists\Components\TextEntry::make('job_class')
                            ->label('Job Class')
                            ->copyable()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('failed_at')
                            ->label('Failed At')
                            ->dateTime()
                            ->since(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Exception Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('exception_class')
                            ->label('Exception Type')
                            ->badge()
                            ->color('danger'),

                        Infolists\Components\TextEntry::make('exception_message')
                            ->label('Exception Message')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('exception')
                            ->label('Full Stack Trace')
                            ->columnSpanFull()
                            ->markdown()
                            ->prose(),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Infolists\Components\Section::make('Job Payload')
                    ->schema([
                        Infolists\Components\TextEntry::make('payload')
                            ->label('Payload')
                            ->columnSpanFull()
                            ->formatStateUsing(function ($state) {
                                $decoded = json_decode($state, true);
                                if ($decoded === null) {
                                    return 'Unable to decode payload';
                                }
                                return json_encode($decoded, JSON_PRETTY_PRINT);
                            })
                            ->markdown()
                            ->prose(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFailedJobs::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('failed_at', 'desc');
    }
}
