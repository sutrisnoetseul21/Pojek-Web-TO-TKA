<?php

namespace App\Filament\Resources\RefPaketSoalResource\Pages;

use App\Filament\Resources\RefPaketSoalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRefPaketSoal extends CreateRecord
{
    protected static string $resource = RefPaketSoalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
