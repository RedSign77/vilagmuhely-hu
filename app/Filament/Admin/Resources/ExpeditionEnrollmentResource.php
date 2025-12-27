<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExpeditionEnrollmentResource\Pages;
use App\Models\ExpeditionEnrollment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ExpeditionEnrollmentResource extends Resource
{
    protected static ?string $model = ExpeditionEnrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Expedition Participants';

    protected static ?string $pluralModelLabel = 'Expedition Participants';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('expedition_id')
                    ->relationship('expedition', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'username')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\DateTimePicker::make('enrolled_at')
                    ->required()
                    ->default(now()),

                Forms\Components\DateTimePicker::make('completed_at')
                    ->nullable(),

                Forms\Components\Toggle::make('reward_claimed')
                    ->default(false),

                Forms\Components\KeyValue::make('progress')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expedition.title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.username')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('enrolled_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->state(function (ExpeditionEnrollment $record): string {
                        $progress = $record->getProgress();
                        return "{$progress['posts_created']}/{$progress['total_required']}";
                    })
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->placeholder('Not completed'),

                Tables\Columns\IconColumn::make('reward_claimed')
                    ->boolean()
                    ->label('Reward'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('expedition')
                    ->relationship('expedition', 'title'),

                Tables\Filters\TernaryFilter::make('completed')
                    ->nullable()
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('completed_at'),
                        false: fn ($query) => $query->whereNull('completed_at'),
                    ),

                Tables\Filters\TernaryFilter::make('reward_claimed')
                    ->nullable()
                    ->queries(
                        true: fn ($query) => $query->where('reward_claimed', true),
                        false: fn ($query) => $query->where('reward_claimed', false),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('force_complete')
                    ->label('Force Complete')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (ExpeditionEnrollment $record) {
                        $record->update(['completed_at' => now()]);
                        app(\App\Services\ExpeditionRewardService::class)->grantRewards($record);
                    })
                    ->visible(fn (ExpeditionEnrollment $record) => !$record->isCompleted()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('enrolled_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('User Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.username')
                            ->label('Username'),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Expedition Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('expedition.title'),
                        Infolists\Components\TextEntry::make('expedition.status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('expedition.starts_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('expedition.ends_at')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Progress Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('progress.posts_created')
                            ->label('Posts Created'),
                        Infolists\Components\TextEntry::make('progress.total_required')
                            ->label('Total Required'),
                        Infolists\Components\TextEntry::make('progress.last_checked_at')
                            ->label('Last Checked')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Timestamps')
                    ->schema([
                        Infolists\Components\TextEntry::make('enrolled_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('completed_at')
                            ->dateTime()
                            ->placeholder('Not completed'),
                        Infolists\Components\IconEntry::make('reward_claimed')
                            ->boolean(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Qualifying Posts')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('qualifyingPosts')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('post.title')
                                    ->label('Post Title'),
                                Infolists\Components\TextEntry::make('qualified_at')
                                    ->label('Qualified At')
                                    ->dateTime(),
                            ])
                            ->columns(2),
                    ])
                    ->visible(fn (ExpeditionEnrollment $record) => $record->qualifyingPosts()->exists()),
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
            'index' => Pages\ListExpeditionEnrollments::route('/'),
            'create' => Pages\CreateExpeditionEnrollment::route('/create'),
            'edit' => Pages\EditExpeditionEnrollment::route('/{record}/edit'),
        ];
    }
}
