<?php

namespace App\Filament\Admin\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Webtechsolutions\ContentEngine\Models\Content;

class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('My Recent Content')
            ->query(
                Content::query()
                    ->where('creator_id', auth()->id())
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\BadgeColumn::make('type')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->colors([
                        'primary' => 'digital_file',
                        'success' => 'image_gallery',
                        'warning' => 'markdown_post',
                        'info' => 'article',
                        'danger' => 'rpg_module',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'preview',
                        'info' => 'members_only',
                        'success' => 'public',
                    ]),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable(),

                Tables\Columns\TextColumn::make('downloads_count')
                    ->label('Downloads')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
