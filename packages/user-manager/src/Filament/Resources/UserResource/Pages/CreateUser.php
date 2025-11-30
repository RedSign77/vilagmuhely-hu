<?php

namespace Webtechsolutions\UserManager\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webtechsolutions\UserManager\Filament\Resources\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
