<?php

namespace App\Filament\Resources\JadwalTryoutResource\Pages;

use App\Filament\Resources\JadwalTryoutResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJadwalTryout extends EditRecord
{
    protected static string $resource = JadwalTryoutResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
