<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;

class SystemSectionHeaderWidget extends Widget
{
    protected static string $view = 'filament.widgets.section-header';

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return '⚙️ System Overview';
    }

    public function getDescription(): string
    {
        return 'Platform-wide statistics and management';
    }

    public static function canView(): bool
    {
        // Only show to admins
        return auth()->user()?->hasRole('admin') ?? false;
    }
}
