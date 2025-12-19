<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\ContentByCategoryWidget;
use App\Filament\Admin\Widgets\ContentPerformanceWidget;
use App\Filament\Admin\Widgets\CreatorSectionHeaderWidget;
use App\Filament\Admin\Widgets\MyContentOverviewWidget;
use App\Filament\Admin\Widgets\MyCrystalStatsWidget;
use App\Filament\Admin\Widgets\PlatformStatsWidget;
use App\Filament\Admin\Widgets\PopularContentWidget;
use App\Filament\Admin\Widgets\RecentActivityWidget;
use App\Filament\Admin\Widgets\RecentUsersWidget;
use App\Filament\Admin\Widgets\SystemOverviewWidget;
use App\Filament\Admin\Widgets\SystemSectionHeaderWidget;
use App\Filament\Admin\Widgets\UserSectionHeaderWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            // User Section - Always visible
            UserSectionHeaderWidget::class,
            MyCrystalStatsWidget::class,
            RecentActivityWidget::class,

            // Creator Section - Only for content creators
            CreatorSectionHeaderWidget::class,
            ContentPerformanceWidget::class,
            PopularContentWidget::class,

            // System Section - Only for admins
            SystemSectionHeaderWidget::class,
            SystemOverviewWidget::class,
            PlatformStatsWidget::class,
            RecentUsersWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }
}
