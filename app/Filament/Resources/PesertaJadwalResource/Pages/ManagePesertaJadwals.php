<?php

namespace App\Filament\Resources\PesertaJadwalResource\Pages;

use App\Filament\Resources\PesertaJadwalResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePesertaJadwals extends ManageRecords
{
    protected static string $resource = PesertaJadwalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
