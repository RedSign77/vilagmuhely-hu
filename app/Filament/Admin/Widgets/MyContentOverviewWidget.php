<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use Webtechsolutions\ContentEngine\Models\Content;

class MyContentOverviewWidget extends ChartWidget
{
    protected static ?string $heading = 'My Content by Type';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $user = auth()->user();

        $contentByType = Content::where('creator_id', $user->id)
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $types = [
            'digital_file' => 'Digital File',
            'image_gallery' => 'Image Gallery',
            'markdown_post' => 'Markdown Post',
            'article' => 'Article',
            'rpg_module' => 'RPG Module',
        ];

        $labels = [];
        $data = [];
        $colors = [
            '#3b82f6', // blue
            '#10b981', // green
            '#f59e0b', // yellow
            '#8b5cf6', // purple
            '#ef4444', // red
        ];

        foreach ($types as $key => $label) {
            if (isset($contentByType[$key]) && $contentByType[$key] > 0) {
                $labels[] = $label;
                $data[] = $contentByType[$key];
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Content Count',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
