<?php

namespace App\Filament\Admin\Widgets;

use App\Models\UserCrystalMetric;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyCrystalStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();
        $metric = UserCrystalMetric::where('user_id', $user->id)->first();

        if (!$metric) {
            return [
                Stat::make('Total Content', '0')
                    ->description('Start creating to grow your crystal')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('warning'),
            ];
        }

        return [
            Stat::make('Total Content', $metric->total_content_count)
                ->description('Pieces of content created')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),

            Stat::make('Crystal Facets', $metric->facet_count)
                ->description('Unique perspectives')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('info'),

            Stat::make('Diversity Index', number_format($metric->diversity_index, 1) . '%')
                ->description('Content variety')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('warning'),

            Stat::make('Interaction Score', number_format($metric->interaction_score, 2))
                ->description('Community engagement')
                ->descriptionIcon('heroicon-m-heart')
                ->color('danger'),
        ];
    }
}
