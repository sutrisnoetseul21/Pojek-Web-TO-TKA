<?php

namespace App\Filament\Resources\ProktorResource\Pages;

use App\Filament\Resources\ProktorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProktor extends EditRecord
{
    protected static string $resource = ProktorResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Jika plain_password dirubah, update hash password-nya juga
        if (!empty($data['plain_password'])) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($data['plain_password']);
        }

        return $data;
    }

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
