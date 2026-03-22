<?php

namespace App\Filament\Resources\JadwalTryoutResource\Pages;

use App\Filament\Resources\JadwalTryoutResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewJadwalTryout extends ViewRecord
{
    protected static string $resource = JadwalTryoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
