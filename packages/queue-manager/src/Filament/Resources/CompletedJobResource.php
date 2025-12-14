<?php

namespace Webtechsolutions\QueueManager\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Webtechsolutions\QueueManager\Filament\Resources\CompletedJobResource\Pages;
use Webtechsolutions\QueueManager\Models\CompletedJob;

class CompletedJobResource extends Resource
{
    protected static ?string $model = CompletedJob::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Completed Jobs';

    protected static ?int $navigationSort = 22;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
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

                Tables\Columns\TextColumn::make('queue')
                    ->label('Queue')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('job_class')
                    ->label('Job Class')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->job_class),

                Tables\Columns\TextColumn::make('attempts')
                    ->label('Attempts')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state == 1 => 'success',
                        $state <= 3 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('execution_time')
                    ->label('Execution Time')
                    ->formatStateUsing(fn ($state) => $state ? $state.'s' : 'N/A')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('queue')
                    ->label('Queue')
                    ->options(function () {
                        return CompletedJob::query()
                            ->select('queue')
                            ->distinct()
                            ->pluck('queue', 'queue')
                            ->toArray();
                    }),

                Tables\Filters\Filter::make('completed_at')
                    ->form([
                        Forms\Components\DatePicker::make('completed_from')
                            ->label('Completed From'),
                        Forms\Components\DatePicker::make('completed_until')
                            ->label('Completed Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['completed_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('completed_at', '>=', $date),
                            )
                            ->when(
                                $data['completed_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('completed_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver()
                    ->infolist(fn (Infolist $infolist) => static::getInfolist($infolist)),

                Tables\Actions\DeleteAction::make()
                    ->label('Clear')
                    ->successNotification(
                        fn () => \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Job cleared')
                            ->body('The completed job record has been removed.')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Clear Selected')
                        ->successNotification(
                            fn () => \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Jobs cleared')
                                ->body('The selected completed jobs have been removed.')
                        ),

                    Tables\Actions\BulkAction::make('clear_all')
                        ->label('Clear All')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Clear All Completed Jobs')
                        ->modalDescription('Are you sure you want to clear ALL completed jobs? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, clear all')
                        ->action(function () {
                            return CompletedJob::query()->delete();
                        })
                        ->successNotification(
                            fn () => \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('All jobs cleared')
                                ->body('All completed jobs have been removed.')
                        )
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('completed_at', 'desc')
            ->poll('30s');
    }

    public static function getInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Completed Job Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('Job ID'),

                        Infolists\Components\TextEntry::make('queue')
                            ->label('Queue')
                            ->badge()
                            ->color('success'),

                        Infolists\Components\TextEntry::make('job_class')
                            ->label('Job Class')
                            ->copyable()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('attempts')
                            ->label('Attempts')
                            ->badge()
                            ->color(fn ($state) => match (true) {
                                $state == 1 => 'success',
                                $state <= 3 => 'warning',
                                default => 'danger',
                            }),

                        Infolists\Components\TextEntry::make('execution_time')
                            ->label('Execution Time')
                            ->formatStateUsing(fn ($state) => $state ? $state.' seconds' : 'Not available'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->formatStateUsing(fn ($state) => Carbon::createFromTimestamp($state)->format('Y-m-d H:i:s').' ('.Carbon::createFromTimestamp($state)->diffForHumans().')'),

                        Infolists\Components\TextEntry::make('available_at')
                            ->label('Available At')
                            ->formatStateUsing(fn ($state) => Carbon::createFromTimestamp($state)->format('Y-m-d H:i:s')),

                        Infolists\Components\TextEntry::make('reserved_at')
                            ->label('Reserved At')
                            ->formatStateUsing(fn ($state) => $state ? Carbon::createFromTimestamp($state)->format('Y-m-d H:i:s') : 'Not reserved')
                            ->visible(fn ($record) => $record->reserved_at),

                        Infolists\Components\TextEntry::make('completed_at')
                            ->label('Completed At')
                            ->dateTime()
                            ->since(),

                        Infolists\Components\TextEntry::make('payload')
                            ->label('Job Payload')
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
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isSupervisor() ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompletedJobs::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('completed_at', 'desc');
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
