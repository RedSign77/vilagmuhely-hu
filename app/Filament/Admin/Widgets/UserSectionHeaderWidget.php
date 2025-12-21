<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;

class UserSectionHeaderWidget extends Widget
{
    protected static string $view = 'filament.widgets.section-header';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return '👤 My Dashboard';
    }

    public function getDescription(): string
    {
        return 'Your personal stats and recent activity';
    }
}
