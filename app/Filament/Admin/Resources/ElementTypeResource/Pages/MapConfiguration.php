<?php

namespace App\Filament\Admin\Resources\ElementTypeResource\Pages;

use App\Filament\Admin\Resources\ElementTypeResource;
use Filament\Resources\Pages\Page;

class MapConfiguration extends Page
{
    protected static string $resource = ElementTypeResource::class;

    protected static string $view = 'filament.admin.resources.element-type-resource.pages.map-configuration';
}
