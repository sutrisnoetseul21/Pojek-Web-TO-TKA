<?php

namespace App\Filament\Resources\BantuanPesertaResource\Pages;

use App\Filament\Resources\BantuanPesertaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBantuanPesertas extends ManageRecords
{
    protected static string $resource = BantuanPesertaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
