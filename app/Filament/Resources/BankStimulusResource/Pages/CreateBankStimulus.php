<?php

namespace App\Filament\Resources\BankStimulusResource\Pages;

use App\Filament\Resources\BankStimulusResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBankStimulus extends CreateRecord
{
    protected static string $resource = BankStimulusResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
