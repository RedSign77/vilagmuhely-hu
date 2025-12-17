<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Webtechsolutions\ContentEngine\Models\Content;
use Illuminate\Support\Facades\DB;

class ContentPerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $user = auth()->user();

        // Check if user has any content
        $hasContent = Content::where('creator_id', $user->id)->exists();

        if (!$hasContent) {
            return [];
        }

        $stats = Content::where('creator_id', $user->id)
            ->selectRaw('
                SUM(views_count) as total_views,
                SUM(downloads_count) as total_downloads,
                AVG(
                    CASE
                        WHEN (
                            SELECT AVG(rating)
                            FROM content_ratings
                            WHERE content_ratings.content_id = contents.id
                        ) IS NOT NULL
                        THEN (
                            SELECT AVG(rating)
                            FROM content_ratings
                            WHERE content_ratings.content_id = contents.id
                        )
                        ELSE 0
                    END
                ) as avg_rating
            ')
            ->first();

        return [
            Stat::make('Total Views', number_format($stats->total_views ?? 0))
                ->description('Across all content')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),

            Stat::make('Total Downloads', number_format($stats->total_downloads ?? 0))
                ->description('Content downloads')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('success'),

            Stat::make('Average Rating', number_format($stats->avg_rating ?? 0, 1) . ' / 5')
                ->description('Community feedback')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
        ];
    }

    public static function canView(): bool
    {
        // Only show for users who have created content
        return Content::where('creator_id', auth()->id())->exists();
    }
}
