<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generateBatch')
                ->label('Generate Batch Users')
                ->icon('heroicon-o-user-plus')
                ->color('success')
                ->form([
                    TextInput::make('jumlah')
                        ->label('Jumlah User')
                        ->numeric()
                        ->required()
                        ->default(100)
                        ->minValue(1)
                        ->maxValue(1000)
                        ->helperText('Maksimal 1000 user per batch'),
                    TextInput::make('tahun')
                        ->label('Tahun Prefix')
                        ->numeric()
                        ->required()
                        ->default(date('Y'))
                        ->helperText('Contoh: 2026 → P2026001'),
                ])
                ->action(function (array $data): StreamedResponse {
                    $jumlah = (int) $data['jumlah'];
                    $tahun = (int) $data['tahun'];
                    $startNumber = UserResource::getNextUsernameNumber($tahun);

                    $users = [];

                    for ($i = 0; $i < $jumlah; $i++) {
                        $number = $startNumber + $i;
                        $username = 'P' . $tahun . str_pad($number, 3, '0', STR_PAD_LEFT);
                        $password = UserResource::generatePassword();

                        $user = User::create([
                            'username' => $username,
                            'name' => $username,
                            'email' => $username . '@tryout.local',
                            'password' => Hash::make($password),
                            'plain_password' => $password,
                            'role' => 'peserta',
                            'is_biodata_complete' => false,
                        ]);

                        $users[] = [
                            'username' => $username,
                            'password' => $password,
                        ];
                    }

                    Notification::make()
                        ->title('Berhasil!')
                        ->body("$jumlah user berhasil dibuat. Download file CSV akan dimulai.")
                        ->success()
                        ->send();

                    // Return CSV download
                    return response()->streamDownload(function () use ($users) {
                        $handle = fopen('php://output', 'w');
                        fputcsv($handle, ['No', 'Username', 'Password']);

                        foreach ($users as $index => $user) {
                            fputcsv($handle, [
                                $index + 1,
                                $user['username'],
                                $user['password'],
                            ]);
                        }

                        fclose($handle);
                    }, 'users_batch_' . date('Y-m-d_His') . '.csv');
                }),

            Actions\CreateAction::make()
                ->label('Tambah User Manual'),
        ];
    }
}

