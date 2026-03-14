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
use Filament\Forms;
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
                    Select::make('sekolah_id')
                        ->label('Sekolah')
                        ->options(\App\Models\Sekolah::pluck('nama_sekolah', 'id'))
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->visible(fn() => auth()->user()->hasRole('super_admin'))
                        ->afterStateUpdated(fn($set) => $set('kelas_id', null)),
                    Select::make('kelas_id')
                        ->label('Kelas')
                        ->options(function (Forms\Get $get) {
                            $user = auth()->user();
                            $query = \App\Models\Kelas::query();
                            
                            if ($user->hasRole('super_admin')) {
                                $sekolahId = $get('sekolah_id');
                                if (!$sekolahId) return [];
                                $query->where('sekolah_id', $sekolahId);
                            } elseif ($user->hasRole('admin') && $user->sekolah_id) {
                                $query->where('sekolah_id', $user->sekolah_id);
                            }
                            
                            return $query->pluck('nama_kelas', 'id');
                        })
                        ->required()
                        ->searchable()
                        ->preload()
                        ->helperText('Wajib memilih kelas untuk penempatan peserta'),
                ])
                ->action(function (array $data) {
                    $jumlah = (int) $data['jumlah'];
                    $kelasId = $data['kelas_id'];
                    $kelas = \App\Models\Kelas::with('sekolah')->find($kelasId);
                    
                    if (!$kelas) {
                        Notification::make()
                            ->title('Gagal!')
                            ->body("Kelas tidak ditemukan.")
                            ->danger()
                            ->send();
                        return;
                    }

                    $prefix = Cache::get('user_username_prefix', 'P' . date('Y') . '00');
                    $startNumber = UserResource::getNextUsernameNumberFromPrefix($prefix);

                    for ($i = 0; $i < $jumlah; $i++) {
                        $number = $startNumber + $i;
                        $username = $prefix . $number;
                        $password = UserResource::generatePassword();
                        $gender = rand(0, 1) ? 'L' : 'P';

                        User::create([
                            'username' => $username,
                            'name' => $username,
                            'nama_lengkap' => $username,
                            'email' => strtolower($username) . '@peserta.local',
                            'password' => Hash::make($password),
                            'plain_password' => $password,
                            'role' => 'peserta',
                            'kelas_id' => $kelasId,
                            'sekolah_id' => $kelas->sekolah_id,
                            'jenjang' => $kelas->jenjang ?? $kelas->sekolah->jenjang ?? null,
                            'jenis_kelamin' => $gender,
                            'is_biodata_complete' => false,
                        ]);
                    }

                    Notification::make()
                        ->title('Berhasil!')
                        ->body("$jumlah user berhasil dibuat untuk kelas {$kelas->nama_kelas}.")
                        ->success()
                        ->send();
                }),

            Actions\Action::make('download_template')
                ->label('Template Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Select::make('sekolah_id')
                        ->label('Sekolah')
                        ->relationship('sekolahRelation', 'nama_sekolah')
                        ->default(fn () => auth()->user()->sekolah_id)
                        ->disabled(fn () => auth()->user()->hasRole('admin'))
                        ->dehydrated()
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live(),
                    Select::make('kelas_id')
                        ->label('Kelas (Opsional)')
                        ->options(function (Forms\Get $get) {
                            $sekolahId = $get('sekolah_id');
                            if (!$sekolahId) return [];
                            return \App\Models\Kelas::where('sekolah_id', $sekolahId)
                                ->pluck('nama_kelas', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->helperText('Jika dipilih, contoh baris di Excel akan menggunakan kelas ini.'),
                ])
                ->action(function (array $data) {
                    $sekolah = \App\Models\Sekolah::find($data['sekolah_id']);
                    $filename = ($sekolah ? $sekolah->nama_sekolah : 'Template') . ' - Template Input User.xlsx';
                    return Excel::download(new UserTemplateExport($data['sekolah_id'], $data['kelas_id']), $filename);
                }),

            Actions\Action::make('import_user')
                ->label('Import Excel')
                ->icon('heroicon-o-document-arrow-up')
                ->color('warning')
                ->form([
                    Select::make('sekolah_id')
                        ->label('Target Sekolah')
                        ->relationship('sekolahRelation', 'nama_sekolah')
                        ->default(fn () => auth()->user()->sekolah_id)
                        ->disabled(fn () => auth()->user()->hasRole('admin'))
                        ->dehydrated()
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live(),
                    Select::make('kelas_id')
                        ->label('Target Kelas Default')
                        ->options(function (Forms\Get $get) {
                            $sekolahId = $get('sekolah_id');
                            if (!$sekolahId) return [];
                            return \App\Models\Kelas::where('sekolah_id', $sekolahId)
                                ->pluck('nama_kelas', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->helperText('Digunakan jika kolom KELAS di Excel kosong'),
                    FileUpload::make('attachment')
                        ->label('File Excel')
                        ->required()
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel']),
                ])
                ->action(function (array $data) {
                    $file = storage_path('app/public/' . $data['attachment']);
                    
                    try {
                        Excel::import(new UserImport($data['sekolah_id'], $data['kelas_id']), $file);
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

