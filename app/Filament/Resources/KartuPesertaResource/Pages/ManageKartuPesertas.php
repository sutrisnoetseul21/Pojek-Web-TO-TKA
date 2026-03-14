<?php

namespace App\Filament\Resources\KartuPesertaResource\Pages;

use App\Filament\Resources\KartuPesertaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageKartuPesertas extends ManageRecords
{
    protected static string $resource = KartuPesertaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No creation needed for Kartu Peserta
        ];
    }
}
