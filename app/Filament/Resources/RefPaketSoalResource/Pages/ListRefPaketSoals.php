<?php

namespace App\Filament\Resources\RefPaketSoalResource\Pages;

use App\Filament\Resources\RefPaketSoalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRefPaketSoals extends ListRecords
{
    protected static string $resource = RefPaketSoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
