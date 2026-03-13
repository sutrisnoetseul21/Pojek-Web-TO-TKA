<?php

namespace App\Filament\Resources\UserAdminResource\Pages;

use App\Filament\Resources\UserAdminResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUserAdmin extends CreateRecord
{
    protected static string $resource = UserAdminResource::class;
}
