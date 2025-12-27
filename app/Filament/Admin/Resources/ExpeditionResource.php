<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExpeditionResource\Pages;
use App\Models\Expedition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ExpeditionResource extends Resource
{
    protected static ?string $model = Expedition::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', \Illuminate\Support\Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Schedule')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->required()
                            ->native(false)
                            ->seconds(false),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->after('starts_at'),

                        Forms\Components\TextInput::make('max_participants')
                            ->numeric()
                            ->nullable()
                            ->minValue(1)
                            ->helperText('Leave blank for unlimited participants'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Requirements')
                    ->schema([
                        Forms\Components\KeyValue::make('requirements')
                            ->keyLabel('Requirement')
                            ->valueLabel('Value')
                            ->default([
                                'content_type' => 'post',
                                'min_word_count' => 500,
                                'required_count' => 3,
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),

                Forms\Components\Section::make('Rewards')
                    ->schema([
                        Forms\Components\KeyValue::make('rewards')
                            ->keyLabel('Reward Type')
                            ->valueLabel('Value')
                            ->default([
                                'crystal_multiplier' => 2.0,
                                'engagement_bonus' => 100,
                                'interaction_bonus' => 50,
                                'visual_effect' => 'expedition_winner_aura',
                                'effect_duration_days' => 30,
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('enrollments_count')
                    ->counts('enrollments')
                    ->label('Participants')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('completion_rate')
                    ->label('Completions')
                    ->state(function (Expedition $record): string {
                        $completed = $record->enrollments()->whereNotNull('completed_at')->count();
                        $total = $record->enrollments()->count();
                        return $total > 0 ? "{$completed}/{$total}" : '0/0';
                    })
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\Filter::make('active_now')
                    ->label('Active Now')
                    ->query(fn ($query) => $query->active()),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('starts_from'),
                        Forms\Components\DatePicker::make('starts_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['starts_from'], fn ($q) => $q->whereDate('starts_at', '>=', $data['starts_from']))
                            ->when($data['starts_until'], fn ($q) => $q->whereDate('starts_at', '<=', $data['starts_until']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Expedition $record) => $record->update(['status' => 'active']))
                    ->visible(fn (Expedition $record) => $record->status === 'draft'),

                Tables\Actions\Action::make('complete')
                    ->label('Complete Early')
                    ->icon('heroicon-o-flag')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(fn (Expedition $record) => $record->update(['status' => 'completed']))
                    ->visible(fn (Expedition $record) => $record->status === 'active'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Basic Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('title'),
                        Infolists\Components\TextEntry::make('slug'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'draft' => 'gray',
                                'active' => 'success',
                                'completed' => 'info',
                                'cancelled' => 'danger',
                            }),
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Schedule')
                    ->schema([
                        Infolists\Components\TextEntry::make('starts_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('ends_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('max_participants')
                            ->default('Unlimited'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Requirements')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('requirements'),
                    ]),

                Infolists\Components\Section::make('Rewards')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('rewards'),
                    ]),

                Infolists\Components\Section::make('Statistics')
                    ->schema([
                        Infolists\Components\TextEntry::make('enrollments_count')
                            ->state(fn (Expedition $record) => $record->getParticipantCount())
                            ->label('Total Participants'),
                        Infolists\Components\TextEntry::make('completed_count')
                            ->state(fn (Expedition $record) => $record->enrollments()->whereNotNull('completed_at')->count())
                            ->label('Completed'),
                        Infolists\Components\TextEntry::make('completion_rate')
                            ->state(fn (Expedition $record) => number_format($record->getCompletionRate(), 1) . '%')
                            ->label('Completion Rate'),
                    ])
                    ->columns(3),
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
            'index' => Pages\ListExpeditions::route('/'),
            'create' => Pages\CreateExpedition::route('/create'),
            'edit' => Pages\EditExpedition::route('/{record}/edit'),
        ];
    }
}
