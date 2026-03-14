<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto generate password jika kosong
        if (empty($data['plain_password'])) {
            $data['plain_password'] = UserResource::generatePassword();
        }

        // Hash password untuk kolom 'password'
        $data['password'] = \Illuminate\Support\Facades\Hash::make($data['plain_password']);

        // Auto generate email untuk peserta (background)
        if (empty($data['email'])) {
            $data['email'] = strtolower($data['username']) . '@peserta.local';
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
