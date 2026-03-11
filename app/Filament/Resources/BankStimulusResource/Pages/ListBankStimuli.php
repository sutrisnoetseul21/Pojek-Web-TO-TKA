<?php

namespace App\Filament\Resources\BankStimulusResource\Pages;

use App\Filament\Resources\BankStimulusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBankStimuli extends ListRecords
{
    protected static string $resource = BankStimulusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
