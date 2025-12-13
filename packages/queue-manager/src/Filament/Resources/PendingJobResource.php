<?php

namespace Webtechsolutions\QueueManager\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Webtechsolutions\QueueManager\Filament\Resources\PendingJobResource\Pages;
use Webtechsolutions\QueueManager\Models\Job;

class PendingJobResource extends Resource
{
    protected static ?string $model = Job::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Pending Jobs';

    protected static ?int $navigationSort = 20;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();

        return $count > 0 ? 'warning' : 'gray';
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
                    ->sortable()
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

                Tables\Columns\TextColumn::make('attempts')
                    ->label('Attempts')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state == 0 => 'success',
                        $state < 3 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Pending' => 'gray',
                        'Processing' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->formatStateUsing(fn ($state) => Carbon::createFromTimestamp($state)->diffForHumans())
                    ->tooltip(fn ($state) => Carbon::createFromTimestamp($state)->format('Y-m-d H:i:s'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('available_at')
                    ->label('Available At')
                    ->formatStateUsing(fn ($state) => Carbon::createFromTimestamp($state)->diffForHumans())
                    ->tooltip(fn ($state) => Carbon::createFromTimestamp($state)->format('Y-m-d H:i:s'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('queue')
                    ->label('Queue')
                    ->options(function () {
                        return Job::query()
                            ->select('queue')
                            ->distinct()
                            ->pluck('queue', 'queue')
                            ->toArray();
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'pending') {
                            return $query->pending();
                        }
                        if ($data['value'] === 'processing') {
                            return $query->processing();
                        }

                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver()
                    ->infolist(fn (Infolist $infolist) => static::getInfolist($infolist)),

                Tables\Actions\Action::make('run_now')
                    ->label('Run Now')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Job $record) {
                        $record->update(['available_at' => now()->timestamp]);
                    })
                    ->successNotification(
                        fn () => \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Job scheduled')
                            ->body('The job has been made available for immediate processing.')
                    ),

                Tables\Actions\Action::make('terminate')
                    ->label('Terminate')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Job $record) {
                        $record->delete();
                    })
                    ->successNotification(
                        fn () => \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Job terminated')
                            ->body('The job has been removed from the queue.')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('run_now')
                        ->label('Run Now')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update(['available_at' => now()->timestamp]);
                                $count++;
                            }

                            return $count;
                        })
                        ->successNotification(
                            fn ($livewire) => \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Jobs scheduled')
                                ->body('The selected jobs have been made available for immediate processing.')
                        ),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Terminate Selected')
                        ->successNotification(
                            fn () => \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Jobs terminated')
                                ->body('The selected jobs have been removed from the queue.')
                        ),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('10s');
    }

    public static function getInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Job Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('Job ID')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('queue')
                            ->label('Queue')
                            ->badge()
                            ->color('gray'),

                        Infolists\Components\TextEntry::make('job_class')
                            ->label('Job Class')
                            ->copyable()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('attempts')
                            ->label('Attempts')
                            ->badge()
                            ->color(fn ($state) => match (true) {
                                $state == 0 => 'success',
                                $state < 3 => 'warning',
                                default => 'danger',
                            }),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'Pending' => 'gray',
                                'Processing' => 'info',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->formatStateUsing(fn ($state) => Carbon::createFromTimestamp($state)->format('Y-m-d H:i:s').' ('.Carbon::createFromTimestamp($state)->diffForHumans().')'),

                        Infolists\Components\TextEntry::make('available_at')
                            ->label('Available At')
                            ->formatStateUsing(fn ($state) => Carbon::createFromTimestamp($state)->format('Y-m-d H:i:s').' ('.Carbon::createFromTimestamp($state)->diffForHumans().')'),

                        Infolists\Components\TextEntry::make('reserved_at')
                            ->label('Reserved At')
                            ->formatStateUsing(fn ($state) => $state ? Carbon::createFromTimestamp($state)->format('Y-m-d H:i:s').' ('.Carbon::createFromTimestamp($state)->diffForHumans().')' : 'Not reserved')
                            ->visible(fn ($record) => $record->reserved_at),

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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPendingJobs::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('created_at', 'desc');
    }
}
