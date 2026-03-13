<?php

namespace App\Filament\Resources\UserAdminResource\Pages;

use App\Filament\Resources\UserAdminResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserAdmins extends ListRecords
{
    protected static string $resource = UserAdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
