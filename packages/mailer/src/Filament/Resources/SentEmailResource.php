<?php

namespace Webtechsolutions\Mailer\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Webtechsolutions\Mailer\Filament\Resources\SentEmailResource\Pages;
use Webtechsolutions\Mailer\Jobs\SendEmailJob;
use Webtechsolutions\Mailer\Models\SentEmail;

class SentEmailResource extends Resource
{
    protected static ?string $model = SentEmail::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Sent Emails';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $failedCount = static::getModel()::where('status', 'failed')->count();
        $queuedCount = static::getModel()::where('status', 'queued')->count();

        if ($failedCount > 0) {
            return 'danger';
        } elseif ($queuedCount > 0) {
            return 'warning';
        }

        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Recipient Information')
                    ->schema([
                        Forms\Components\TextInput::make('recipient_name')
                            ->label('Recipient Name'),

                        Forms\Components\TextInput::make('recipient_email')
                            ->label('Recipient Email')
                            ->email(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Email Details')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->columnSpanFull(),

                        Forms\Components\MarkdownEditor::make('body')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Status Information')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'queued' => 'Queued',
                                'sent' => 'Sent',
                                'failed' => 'Failed',
                            ])
                            ->disabled(),

                        Forms\Components\Textarea::make('error_message')
                            ->columnSpanFull()
                            ->disabled()
                            ->visible(fn ($record) => $record?->status === 'failed'),

                        Forms\Components\DateTimePicker::make('sent_at')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('created_at')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recipient_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('recipient_email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),

                Tables\Columns\TextColumn::make('emailTemplate.name')
                    ->label('Template')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'sent',
                        'warning' => 'queued',
                        'danger' => 'failed',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Queued At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'queued' => 'Queued',
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver(),

                Tables\Actions\Action::make('retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (SentEmail $record) => $record->status === 'failed')
                    ->requiresConfirmation()
                    ->action(function (SentEmail $record) {
                        $record->update(['status' => 'queued', 'error_message' => null]);
                        SendEmailJob::dispatch($record->id);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('10s');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isSupervisor() ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSentEmails::route('/'),
        ];
    }
}
