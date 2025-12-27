<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvitationResource\Pages;
use App\Models\Invitation;
use App\Notifications\InvitationNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Notification;

class InvitationResource extends Resource
{
    protected static ?string $model = Invitation::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invitation Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('message')
                            ->rows(3)
                            ->maxLength(500),

                        Forms\Components\Select::make('invited_by_user_id')
                            ->label('Invited By')
                            ->relationship('invitedBy', 'name')
                            ->default(auth()->id())
                            ->required()
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'accepted' => 'Accepted',
                                'expired' => 'Expired',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                Tables\Columns\TextColumn::make('invitedBy.name')
                    ->label('Invited By')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'accepted' => 'success',
                        'expired' => 'danger',
                        'cancelled' => 'gray',
                    }),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->color(fn (Invitation $record) => $record->isExpired() ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('accepted_at')
                    ->label('Accepted')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->placeholder('Not yet'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sent')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('invited_by')
                    ->relationship('invitedBy', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('expired')
                    ->query(fn ($query) => $query->where('expires_at', '<', now()))
                    ->label('Expired Only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver(),

                Tables\Actions\Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (Invitation $record) => $record->status === 'pending' && !$record->isExpired())
                    ->requiresConfirmation()
                    ->action(function (Invitation $record) {
                        Notification::route('mail', $record->email)
                            ->notify(new InvitationNotification($record));

                        FilamentNotification::make()
                            ->title('Invitation resent')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Invitation $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Invitation $record) {
                        $record->update(['status' => 'cancelled']);

                        FilamentNotification::make()
                            ->title('Invitation cancelled')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('copy_link')
                    ->label('Copy Link')
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->visible(fn (Invitation $record) => $record->status === 'pending' && !$record->isExpired())
                    ->action(function (Invitation $record) {
                        $url = route('invitations.accept', $record->token);

                        // Copy to clipboard using JavaScript
                        $this->js("
                            navigator.clipboard.writeText('$url').then(function() {
                                console.log('Copied to clipboard');
                            });
                        ");

                        FilamentNotification::make()
                            ->title('Link copied to clipboard!')
                            ->body('The invitation link has been copied and is ready to share.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('cancel')
                        ->label('Cancel Invitations')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'cancelled'])),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Invitation Information')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')
                            ->copyable()
                            ->icon('heroicon-o-envelope'),
                        TextEntry::make('message')
                            ->placeholder('No message'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'accepted' => 'success',
                                'expired' => 'danger',
                                'cancelled' => 'gray',
                            }),
                    ])
                    ->columns(2),

                Section::make('Invitation Details')
                    ->schema([
                        TextEntry::make('invitedBy.name')
                            ->label('Invited By'),
                        TextEntry::make('acceptedBy.name')
                            ->label('Accepted By')
                            ->placeholder('Not accepted yet'),
                        TextEntry::make('created_at')
                            ->label('Sent At')
                            ->dateTime(),
                        TextEntry::make('expires_at')
                            ->label('Expires At')
                            ->dateTime()
                            ->color(fn (Invitation $record) => $record->isExpired() ? 'danger' : 'success'),
                        TextEntry::make('accepted_at')
                            ->label('Accepted At')
                            ->dateTime()
                            ->placeholder('Not accepted yet'),
                        TextEntry::make('token')
                            ->label('Invitation Token')
                            ->copyable()
                            ->icon('heroicon-o-key'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvitations::route('/'),
            'create' => Pages\CreateInvitation::route('/create'),
            'edit' => Pages\EditInvitation::route('/{record}/edit'),
            'view' => Pages\ViewInvitation::route('/{record}'),
        ];
    }
}
