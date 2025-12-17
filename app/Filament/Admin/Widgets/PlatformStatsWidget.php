<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use Webtechsolutions\ContentEngine\Models\Content;
use Illuminate\Support\Facades\DB;

class PlatformStatsWidget extends ChartWidget
{
    protected static ?string $heading = 'Content Created Over Time';
    protected static ?int $sort = 12;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Get content created in the last 12 months
        $contentByMonth = Content::query()
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Fill in missing months with 0
        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->format('M Y');
            $data[] = $contentByMonth[$month] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Content Created',
                    'data' => $data,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public static function canView(): bool
    {
        // Only show to admins
        return auth()->user()?->hasRole('admin') ?? false;
    }
}
