<?php

namespace App\Filament\Resources\ProktorResource\Pages;

use App\Filament\Resources\ProktorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProktor extends CreateRecord
{
    protected static string $resource = ProktorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto generate password jika kosong
        if (empty($data['plain_password'])) {
            $data['plain_password'] = ProktorResource::generatePassword();
        }

        // Hash password untuk kolom 'password'
        $data['password'] = \Illuminate\Support\Facades\Hash::make($data['plain_password']);

        // Auto generate email untuk proktor (background)
        if (empty($data['email'])) {
            $data['email'] = strtolower($data['username']) . '@proktor.local';
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Otomatis menetapkan role 'proktor' dari Spatie setelah berhasil dibuat
        $this->record->assignRole('proktor');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
