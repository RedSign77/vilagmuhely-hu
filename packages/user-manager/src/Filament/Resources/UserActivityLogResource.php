<?php

namespace Webtechsolutions\UserManager\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Webtechsolutions\UserManager\Filament\Resources\UserActivityLogResource\Pages;
use Webtechsolutions\UserManager\Models\UserActivityLog;

class UserActivityLogResource extends Resource
{
    protected static ?string $model = UserActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'User Activity Logs';

    protected static ?string $modelLabel = 'Activity Log';

    protected static ?string $pluralModelLabel = 'Activity Logs';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Activities today';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Activity Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('activity_type')
                            ->options(UserActivityLog::getActivityTypes())
                            ->required(),

                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address'),

                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('user_agent')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\KeyValue::make('properties')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->default('Unknown User'),

                Tables\Columns\TextColumn::make('activity_type_label')
                    ->label('Activity')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Login' => 'success',
                        'Logout' => 'info',
                        'Failed Login' => 'danger',
                        'Profile Change' => 'warning',
                        'Password Change' => 'warning',
                        'Role Change' => 'primary',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Login' => 'heroicon-o-arrow-right-on-rectangle',
                        'Logout' => 'heroicon-o-arrow-left-on-rectangle',
                        'Failed Login' => 'heroicon-o-shield-exclamation',
                        'Profile Change' => 'heroicon-o-user',
                        'Password Change' => 'heroicon-o-key',
                        'Role Change' => 'heroicon-o-shield-check',
                        default => 'heroicon-o-information-circle',
                    })
                    ->searchable(query: function ($query, $search) {
                        return $query->where('activity_type', 'like', "%{$search}%");
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->copyable()
                    ->url(fn ($record) => $record->ip_address ? 'https://whatismyipaddress.com/ip/' . $record->ip_address : null)
                    ->openUrlInNewTab()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('browser')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('platform')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('M d, Y H:i:s')
                    ->sortable()
                    ->since()
                    ->description(fn ($record) => $record->created_at->format('M d, Y H:i:s')),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('activity_type')
                    ->label('Activity Type')
                    ->options(UserActivityLog::getActivityTypes())
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'From ' . \Carbon\Carbon::parse($data['from'])->toFormattedDateString();
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Until ' . \Carbon\Carbon::parse($data['until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->poll('10s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserActivityLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
