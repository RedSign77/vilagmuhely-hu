<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Webtechsolutions\ContentEngine\Models\Content;

class SystemOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 10;

    protected function getStats(): array
    {
        $totalUsers = User::count();
        $totalContent = Content::count();
        $publicContent = Content::where('status', Content::STATUS_PUBLIC)->count();
        $totalViews = Content::sum('views_count');

        return [
            Stat::make('Total Users', number_format($totalUsers))
                ->description('Registered creators')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->chart([10, 15, 20, 18, 25, 30, 35]),

            Stat::make('Total Content', number_format($totalContent))
                ->description($publicContent.' public')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Total Views', number_format($totalViews))
                ->description('Across all content')
                ->descriptionIcon('heroicon-m-eye')
                ->color('warning'),

            Stat::make('Avg Content/User', number_format($totalUsers > 0 ? $totalContent / $totalUsers : 0, 1))
                ->description('Content creation rate')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),
        ];
    }

    public static function canView(): bool
    {
        // Only show to admins
        return auth()->user()?->hasRole('admin') ?? false;
    }
}
