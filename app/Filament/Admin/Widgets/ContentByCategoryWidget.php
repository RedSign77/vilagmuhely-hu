<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use Webtechsolutions\ContentEngine\Models\Content;

class ContentByCategoryWidget extends ChartWidget
{
    protected static ?string $heading = 'My Content by Category';
    protected static ?int $sort = 6;

    protected function getData(): array
    {
        $user = auth()->user();

        $contentByCategory = Content::query()
            ->where('creator_id', $user->id)
            ->join('content_categories', 'contents.category_id', '=', 'content_categories.id')
            ->selectRaw('content_categories.name as category, count(*) as count')
            ->groupBy('content_categories.id', 'content_categories.name')
            ->pluck('count', 'category')
            ->toArray();

        // Add uncategorized count
        $uncategorized = Content::where('creator_id', $user->id)
            ->whereNull('category_id')
            ->count();

        if ($uncategorized > 0) {
            $contentByCategory['Uncategorized'] = $uncategorized;
        }

        if (empty($contentByCategory)) {
            return [
                'datasets' => [
                    [
                        'label' => 'Content Count',
                        'data' => [0],
                        'backgroundColor' => ['#9ca3af'],
                    ],
                ],
                'labels' => ['No content yet'],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Content Count',
                    'data' => array_values($contentByCategory),
                    'backgroundColor' => [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#8b5cf6',
                        '#ef4444',
                        '#06b6d4',
                        '#ec4899',
                        '#84cc16',
                    ],
                ],
            ],
            'labels' => array_keys($contentByCategory),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    public static function canView(): bool
    {
        // Only show for users who have content
        return Content::where('creator_id', auth()->id())->exists();
    }
}
