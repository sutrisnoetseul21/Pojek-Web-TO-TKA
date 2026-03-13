<?php

namespace App\Filament\Resources\UserSuperAdminResource\Pages;

use App\Filament\Resources\UserSuperAdminResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUserSuperAdmin extends CreateRecord
{
    protected static string $resource = UserSuperAdminResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
