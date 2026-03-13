<?php

namespace App\Filament\Resources\UserSuperAdminResource\Pages;

use App\Filament\Resources\UserSuperAdminResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserSuperAdmins extends ListRecords
{
    protected static string $resource = UserSuperAdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
