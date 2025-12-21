<?php

namespace App\Filament\Admin\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Webtechsolutions\ContentEngine\Models\Content;

class PopularContentWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('My Top Performing Content')
            ->query(
                Content::query()
                    ->where('creator_id', auth()->id())
                    ->where('status', Content::STATUS_PUBLIC)
                    ->orderByDesc('views_count')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\BadgeColumn::make('type')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->colors([
                        'primary' => 'digital_file',
                        'success' => 'image_gallery',
                        'warning' => 'markdown_post',
                        'info' => 'article',
                        'danger' => 'rpg_module',
                    ]),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->default('—'),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable()
                    ->icon('heroicon-m-eye')
                    ->iconColor('info'),

                Tables\Columns\TextColumn::make('downloads_count')
                    ->label('Downloads')
                    ->sortable()
                    ->icon('heroicon-m-arrow-down-tray')
                    ->iconColor('success'),

                Tables\Columns\TextColumn::make('average_rating')
                    ->label('Rating')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1).' / 5' : '—')
                    ->icon('heroicon-m-star')
                    ->iconColor('warning'),
            ])
            ->paginated(false);
    }

    public static function canView(): bool
    {
        // Only show for users who have public content
        return Content::where('creator_id', auth()->id())
            ->where('status', Content::STATUS_PUBLIC)
            ->exists();
    }
}
