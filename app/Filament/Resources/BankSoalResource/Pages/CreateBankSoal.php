<?php

namespace App\Filament\Resources\BankSoalResource\Pages;

use App\Filament\Resources\BankSoalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBankSoal extends CreateRecord
{
    protected static string $resource = BankSoalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
