<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;
use Webtechsolutions\ContentEngine\Models\Content;

class CreatorSectionHeaderWidget extends Widget
{
    protected static string $view = 'filament.widgets.section-header';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return '🎨 Creator Analytics';
    }

    public function getDescription(): string
    {
        return 'Performance insights for your published content';
    }

    public static function canView(): bool
    {
        // Only show for users who have content
        return Content::where('creator_id', auth()->id())->exists();
    }
}
