<?php

/*
 * Webtech-solutions 2025, All rights reserved.
 */

namespace App\Filament\Admin\Resources;

use App\Models\ScheduledEmail;
use App\Models\User;
use Cron\CronExpression;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ScheduledEmailResource extends Resource
{
    protected static ?string $model = ScheduledEmail::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 7;

    public static function canAccess(): bool
    {
        return auth()->user()->isSupervisor();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Descriptive name for this scheduled email campaign'),
                        Forms\Components\Select::make('email_template_id')
                            ->label('Email Template')
                            ->relationship('emailTemplate', 'code', fn ($query) => $query->orderBy('code'))
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} - {$record->subject}")
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Toggle::make('is_enabled')
                            ->label('Enable Schedule')
                            ->default(true)
                            ->helperText('Global toggle to enable/disable this scheduled email'),
                    ])->columns(2),

                Forms\Components\Section::make('Schedule Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('cron_expression')
                            ->label('Cron Expression')
                            ->required()
                            ->placeholder('* * * * *')
                            ->helperText('5-part cron expression (minute hour day month weekday)')
                            ->rule('regex:/^(\*|[0-9,\-\/]+)\s+(\*|[0-9,\-\/]+)\s+(\*|[0-9,\-\/]+)\s+(\*|[0-9,\-\/]+)\s+(\*|[0-9,\-\/]+)$/')
                            ->rule(function () {
                                return function (string $attribute, $value, \Closure $fail) {
                                    try {
                                        new CronExpression($value);
                                    } catch (\Exception $e) {
                                        $fail('Invalid cron expression format.');
                                    }
                                };
                            })
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    try {
                                        $cron = new CronExpression($state);
                                        $next = $cron->getNextRunDate();
                                        $set('next_run_at', $next);
                                    } catch (\Exception $e) {
                                        // Invalid cron
                                    }
                                }
                            }),
                        Forms\Components\Placeholder::make('cron_help')
                            ->label('Common Patterns')
                            ->content(new HtmlString('
                                <div class="text-sm space-y-1">
                                    <div><code>*/5 * * * *</code> - Every 5 minutes</div>
                                    <div><code>0 * * * *</code> - Every hour</div>
                                    <div><code>0 9 * * *</code> - Every day at 9:00 AM</div>
                                    <div><code>0 9 * * 1</code> - Every Monday at 9:00 AM</div>
                                    <div><code>0 0 1 * *</code> - First day of every month</div>
                                </div>
                            ')),
                        Forms\Components\DateTimePicker::make('next_run_at')
                            ->label('Next Scheduled Run')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),

                Forms\Components\Section::make('Data Source & Recipients')
                    ->schema([
                        Forms\Components\Select::make('data_source')
                            ->label('Data Source')
                            ->options([
                                'users' => 'Users',
                                'orders' => 'Orders',
                            ])
                            ->required()
                            ->default('users')
                            ->live()
                            ->helperText('Select the source of data for email recipients'),

                        Forms\Components\Select::make('recipient_type')
                            ->label('Recipient Selection')
                            ->options([
                                'all' => 'All Users',
                                'roles' => 'Specific Roles',
                                'individual' => 'Individual Users',
                            ])
                            ->required()
                            ->default('all')
                            ->live()
                            ->visible(fn (Get $get) => $get('data_source') === 'users'),

                        Forms\Components\Select::make('recipient_roles')
                            ->label('Select Roles')
                            ->multiple()
                            ->searchable()
                            ->options([
                                'supervisor' => 'Supervisor',
                                'user' => 'Regular User',
                            ])
                            ->visible(fn (Get $get) => $get('data_source') === 'users' && $get('recipient_type') === 'roles')
                            ->helperText('Select one or more roles to target'),

                        Forms\Components\Select::make('recipient_users')
                            ->label('Select Users')
                            ->multiple()
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                return User::where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->getOptionLabelsUsing(function (array $values): array {
                                return User::whereIn('id', $values)
                                    ->get()
                                    ->mapWithKeys(fn ($user) => [$user->id => "{$user->name} ({$user->email})"])
                                    ->toArray();
                            })
                            ->visible(fn (Get $get) => $get('data_source') === 'users' && $get('recipient_type') === 'individual')
                            ->helperText('Search and select specific users'),

                        Forms\Components\Select::make('order_statuses')
                            ->label('Order Statuses')
                            ->multiple()
                            ->options([
                                'pending' => 'Pending',
                                'packing' => 'Packing',
                                'paid' => 'Paid',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->visible(fn (Get $get) => $get('data_source') === 'orders')
                            ->helperText('Filter orders by status'),

                        Forms\Components\TextInput::make('lookback_hours')
                            ->label('Look-back Window (hours)')
                            ->numeric()
                            ->default(24)
                            ->minValue(1)
                            ->visible(fn (Get $get) => $get('data_source') === 'orders')
                            ->helperText('Only include orders updated within the last X hours'),
                    ])->columns(2),

                Forms\Components\Section::make('Execution Statistics')
                    ->schema([
                        Forms\Components\Placeholder::make('last_run_at')
                            ->label('Last Executed')
                            ->content(fn ($record) => $record?->last_run_at?->diffForHumans() ?? 'Never'),
                        Forms\Components\Placeholder::make('total_sent')
                            ->label('Total Emails Sent')
                            ->content(fn ($record) => number_format($record?->total_sent ?? 0)),
                    ])
                    ->columns(2)
                    ->hidden(fn ($record) => ! $record),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('emailTemplate.code')
                    ->label('Template')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data_source')
                    ->label('Source')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'users' => 'success',
                        'orders' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('cron_expression')
                    ->label('Schedule')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('next_run_at')
                    ->label('Next Run')
                    ->dateTime()
                    ->since()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_sent')
                    ->label('Sent')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_run_at')
                    ->label('Last Run')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('data_source')
                    ->options([
                        'users' => 'Users',
                        'orders' => 'Orders',
                    ]),
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\Action::make('force_run')
                    ->label('Force Run')
                    ->icon('heroicon-o-bolt')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Force Execute Scheduled Email')
                    ->modalDescription(fn ($record) => "This will execute '{$record->name}' immediately, bypassing the scheduled time ({$record->next_run_at?->format('Y-m-d H:i')}).")
                    ->modalSubmitActionLabel('Execute Now')
                    ->action(function (ScheduledEmail $record) {
                        // Capture command output
                        \Artisan::call('emails:process-scheduled', ['--id' => $record->id]);
                        $output = \Artisan::output();

                        // Parse output for sent count
                        preg_match('/Sent: (\d+)/', $output, $matches);
                        $sent = $matches[1] ?? 0;

                        preg_match('/Skipped.*?: (\d+)/', $output, $skippedMatches);
                        $skipped = $skippedMatches[1] ?? 0;

                        // Show detailed notification
                        \Filament\Notifications\Notification::make()
                            ->title('Email Campaign Executed')
                            ->body("Sent: {$sent} | Skipped: {$skipped}")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('force_run_bulk')
                        ->label('Force Run Selected')
                        ->icon('heroicon-o-bolt')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Force Execute Multiple Campaigns')
                        ->modalDescription('This will execute all selected scheduled emails immediately, bypassing their scheduled times.')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $totalSent = 0;
                            $totalSkipped = 0;

                            foreach ($records as $record) {
                                \Artisan::call('emails:process-scheduled', ['--id' => $record->id]);
                                $output = \Artisan::output();

                                preg_match('/Sent: (\d+)/', $output, $matches);
                                $totalSent += $matches[1] ?? 0;

                                preg_match('/Skipped.*?: (\d+)/', $output, $skippedMatches);
                                $totalSkipped += $skippedMatches[1] ?? 0;
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Bulk Email Campaigns Executed')
                                ->body("{$records->count()} campaign(s) executed | Sent: {$totalSent} | Skipped: {$totalSkipped}")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => \App\Filament\Admin\Resources\ScheduledEmailResource\Pages\ListScheduledEmails::route('/'),
            'create' => \App\Filament\Admin\Resources\ScheduledEmailResource\Pages\CreateScheduledEmail::route('/create'),
            'edit' => \App\Filament\Admin\Resources\ScheduledEmailResource\Pages\EditScheduledEmail::route('/{record}/edit'),
        ];
    }
}
