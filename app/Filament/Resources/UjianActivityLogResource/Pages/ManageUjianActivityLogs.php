<?php

namespace App\Filament\Resources\UjianActivityLogResource\Pages;

use App\Filament\Resources\UjianActivityLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageUjianActivityLogs extends ManageRecords
{
    protected static string $resource = UjianActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
