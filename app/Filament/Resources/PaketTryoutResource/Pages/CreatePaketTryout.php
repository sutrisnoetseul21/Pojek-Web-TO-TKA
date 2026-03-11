<?php

namespace App\Filament\Resources\PaketTryoutResource\Pages;

use App\Filament\Resources\PaketTryoutResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePaketTryout extends CreateRecord
{
    protected static string $resource = PaketTryoutResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
