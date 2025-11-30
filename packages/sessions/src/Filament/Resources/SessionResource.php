<?php

namespace Webtechsolutions\Sessions\Filament\Resources;

use Webtechsolutions\Sessions\Filament\Resources\SessionResource\Pages;
use Webtechsolutions\Sessions\Models\Session;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\DB;

class SessionResource extends Resource
{
    protected static ?string $model = Session::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Sessions';

    protected static ?int $navigationSort = 10;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
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
                    ->label('Session ID')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->id),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->default('Guest'),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->url(fn ($record) => $record->ip_address
                        ? "https://whatismyipaddress.com/ip/{$record->ip_address}"
                        : null)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->icon('heroicon-m-arrow-top-right-on-square'),

                Tables\Columns\TextColumn::make('user_agent')
                    ->label('User Agent')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->user_agent)
                    ->searchable(),

                Tables\Columns\TextColumn::make('last_activity')
                    ->label('Last Activity')
                    ->dateTime()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::createFromTimestamp($state)->diffForHumans())
                    ->tooltip(fn ($state) => \Carbon\Carbon::createFromTimestamp($state)->format('Y-m-d H:i:s')),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Active (Last 5 minutes)')
                    ->query(fn ($query) => $query->where('last_activity', '>=', now()->subMinutes(5)->timestamp)),

                Tables\Filters\Filter::make('authenticated')
                    ->label('Authenticated Users')
                    ->query(fn ($query) => $query->whereNotNull('user_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver()
                    ->infolist(fn (Infolist $infolist) => static::getInfolist($infolist)),

                Tables\Actions\Action::make('destroy')
                    ->label('Destroy')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Session $record) {
                        // Delete the session from the database
                        $record->delete();

                        // Also clear from cache if using cache driver
                        if (config('session.driver') === 'database') {
                            DB::table('sessions')->where('id', $record->id)->delete();
                        }
                    })
                    ->successNotification(
                        fn () => \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Session destroyed')
                            ->body('The session has been successfully removed.')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Destroy Selected')
                        ->successNotification(
                            fn () => \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Sessions destroyed')
                                ->body('The selected sessions have been successfully removed.')
                        ),
                ]),
            ])
            ->defaultSort('last_activity', 'desc');
    }

    public static function getInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Session Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('Session ID')
                            ->copyable()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('User')
                            ->default('Guest'),

                        Infolists\Components\TextEntry::make('user.email')
                            ->label('User Email')
                            ->visible(fn ($record) => $record->user_id !== null)
                            ->default('N/A'),

                        Infolists\Components\TextEntry::make('ip_address')
                            ->label('IP Address')
                            ->url(fn ($record) => $record->ip_address
                                ? "https://whatismyipaddress.com/ip/{$record->ip_address}"
                                : null)
                            ->openUrlInNewTab()
                            ->color('primary')
                            ->icon('heroicon-m-arrow-top-right-on-square')
                            ->default('Unknown'),

                        Infolists\Components\TextEntry::make('user_agent')
                            ->label('User Agent')
                            ->columnSpanFull()
                            ->default('Unknown'),

                        Infolists\Components\TextEntry::make('last_activity')
                            ->label('Last Activity')
                            ->formatStateUsing(fn ($state) => \Carbon\Carbon::createFromTimestamp($state)->format('Y-m-d H:i:s') . ' (' . \Carbon\Carbon::createFromTimestamp($state)->diffForHumans() . ')'),

                        Infolists\Components\TextEntry::make('payload')
                            ->label('Session Payload')
                            ->columnSpanFull()
                            ->formatStateUsing(function ($state) {
                                // Decode and format the payload for better readability
                                $decoded = base64_decode($state);
                                if ($decoded === false) {
                                    return 'Unable to decode payload';
                                }

                                // Try to unserialize
                                $unserialized = @unserialize($decoded);
                                if ($unserialized === false) {
                                    return substr($decoded, 0, 500) . (strlen($decoded) > 500 ? '...' : '');
                                }

                                return json_encode($unserialized, JSON_PRETTY_PRINT);
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
            'index' => Pages\ListSessions::route('/'),
        ];
    }
}
