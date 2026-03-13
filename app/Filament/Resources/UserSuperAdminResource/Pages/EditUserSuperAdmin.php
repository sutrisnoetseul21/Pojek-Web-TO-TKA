<?php

namespace App\Filament\Resources\UserSuperAdminResource\Pages;

use App\Filament\Resources\UserSuperAdminResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserSuperAdmin extends EditRecord
{
    protected static string $resource = UserSuperAdminResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
