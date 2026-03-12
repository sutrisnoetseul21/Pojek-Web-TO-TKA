<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Exports\UserTemplateExport;
use App\Imports\UserImport;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Manual'),

            Actions\Action::make('cetak_kartu')
                ->label('Cetak Kartu')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->form([
                    Select::make('sekolah')
                        ->label('Filter Sekolah')
                        ->options(function () {
                            $sekolahList = ['semua' => '— Semua Sekolah —'];
                            $sekolahFromDb = User::where('role', 'peserta')
                                ->whereNotNull('sekolah')
                                ->where('sekolah', '!=', '')
                                ->distinct()
                                ->orderBy('sekolah')
                                ->pluck('sekolah', 'sekolah')
                                ->toArray();
                            return $sekolahList + $sekolahFromDb;
                        })
                        ->required()
                        ->default('semua')
                        ->searchable()
                        ->helperText('Pilih sekolah tertentu atau cetak semua sekolah sekaligus.'),
                ])
                ->action(function (array $data) {
                    $url = route('print.kartu-peserta', ['sekolah' => $data['sekolah']]);
                    $this->redirect($url);
                }),

            Actions\Action::make('settings')
                ->label('Pengaturan Prefix')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->form([
                    TextInput::make('prefix')
                        ->label('Awalan Username (Prefix)')
                        ->required()
                        ->default(fn () => Cache::get('user_username_prefix', 'P' . date('Y') . '00'))
                        ->helperText('Contoh: P202600. Sistem akan otomatis menambahkan urutan di belakangnya (P2026001, P202600100).'),
                ])
                ->action(function (array $data) {
                    Cache::forever('user_username_prefix', strtoupper($data['prefix']));
                    Notification::make()
                        ->title('Berhasil disimpan')
                        ->body('Prefix username default telah diubah menjadi ' . strtoupper($data['prefix']))
                        ->success()
                        ->send();
                }),

            Actions\Action::make('generateBatch')
                ->label('Generate Batch')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->form([
                    TextInput::make('jumlah')
                        ->label('Jumlah User')
                        ->numeric()
                        ->required()
                        ->default(100)
                        ->minValue(1)
                        ->maxValue(1000)
                        ->helperText('Maksimal 1000 user per batch'),
                ])
                ->action(function (array $data): StreamedResponse {
                    $jumlah = (int) $data['jumlah'];
                    $prefix = Cache::get('user_username_prefix', 'P' . date('Y') . '00');
                    $startNumber = UserResource::getNextUsernameNumberFromPrefix($prefix);

                    $users = [];

                    for ($i = 0; $i < $jumlah; $i++) {
                        $number = $startNumber + $i;
                        $username = $prefix . $number;
                        $password = UserResource::generatePassword();

                        User::create([
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
                        ->body("$jumlah user berhasil dibuat dengan awalan $prefix.")
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

            Actions\Action::make('download_template')
                ->label('Template Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    return Excel::download(new UserTemplateExport, 'Template_Input_User.xlsx');
                }),

            Actions\Action::make('import_user')
                ->label('Import Excel')
                ->icon('heroicon-o-document-arrow-up')
                ->color('warning')
                ->form([
                    FileUpload::make('attachment')
                        ->label('File Excel')
                        ->required()
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel']),
                ])
                ->action(function (array $data) {
                    $file = storage_path('app/public/' . $data['attachment']);
                    
                    try {
                        Excel::import(new UserImport, $file);
                        Notification::make()
                            ->title('Berhasil import User')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal import User')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}

