<?php

namespace App\Filament\Resources\PaketTryoutResource\Pages;

use App\Filament\Resources\PaketTryoutResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaketTryout extends EditRecord
{
    protected static string $resource = PaketTryoutResource::class;

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
