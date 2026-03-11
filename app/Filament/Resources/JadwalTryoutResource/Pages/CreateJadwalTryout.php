<?php

namespace App\Filament\Resources\JadwalTryoutResource\Pages;

use App\Filament\Resources\JadwalTryoutResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateJadwalTryout extends CreateRecord
{
    protected static string $resource = JadwalTryoutResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
