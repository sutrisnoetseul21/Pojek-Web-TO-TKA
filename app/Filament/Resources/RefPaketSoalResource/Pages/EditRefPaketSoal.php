<?php

namespace App\Filament\Resources\RefPaketSoalResource\Pages;

use App\Filament\Resources\RefPaketSoalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRefPaketSoal extends EditRecord
{
    protected static string $resource = RefPaketSoalResource::class;

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
