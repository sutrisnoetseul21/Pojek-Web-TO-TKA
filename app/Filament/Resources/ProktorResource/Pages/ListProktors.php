<?php

namespace App\Filament\Resources\ProktorResource\Pages;

use App\Filament\Resources\ProktorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProktors extends ListRecords
{
    protected static string $resource = ProktorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Proktor'),
        ];
    }
}

