<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentUsersWidget extends BaseWidget
{
    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent User Registrations')
            ->query(
                User::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('roles.name')
                    ->label('Roles')
                    ->colors([
                        'danger' => 'admin',
                        'warning' => 'moderator',
                        'success' => 'creator',
                        'secondary' => 'user',
                    ]),

                Tables\Columns\TextColumn::make('contents_count')
                    ->counts('contents')
                    ->label('Content')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->paginated(false);
    }

    public static function canView(): bool
    {
        // Only show to admins
        return auth()->user()?->hasRole('admin') ?? false;
    }
}
