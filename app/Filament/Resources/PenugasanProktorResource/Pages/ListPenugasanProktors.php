<?php

namespace App\Filament\Resources\PenugasanProktorResource\Pages;

use App\Filament\Resources\PenugasanProktorResource;
use App\Models\JadwalRuanganProktor;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\Rule;

class ListPenugasanProktors extends ListRecords
{
    protected static string $resource = PenugasanProktorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_bulk')
                ->label('New Pengaturan Server Proktor')
                ->icon('heroicon-o-plus')
                ->color('warning')
                ->visible(fn () => auth()->user()->hasRole(['super_admin', 'admin']))
                ->form([
                    Forms\Components\Select::make('proktor_id')
                        ->label('Nama Proktor')
                        ->options(function () {
                            return User::where('role', 'proktor')
                                ->when(auth()->user()->hasRole('admin'), fn ($q) => $q->where('sekolah_id', auth()->user()->sekolah_id))
                                ->pluck('nama_lengkap', 'id')
                                ->toArray();
                        })
                        ->required()
                        ->native(false)
                        ->searchable()
                        ->columnSpanFull(),

                    Forms\Components\Repeater::make('penugasan')
                        ->label('Daftar Penugasan')
                        ->schema([
                            Forms\Components\Select::make('jadwal_tryout_id')
                                ->label('Jadwal Ujian')
                                ->options(function () {
                                    $sekolahId = auth()->user()->sekolah_id;
                                    return \App\Models\JadwalTryout::with('paketTryout')
                                        ->when($sekolahId, fn ($q) => $q->where('sekolah_id', $sekolahId))
                                        ->get()
                                        ->mapWithKeys(fn ($j) => [$j->id => ($j->paketTryout->nama_paket ?? 'Tanpa Paket') . ' (' . ($j->nama_sesi ?? 'Sesi') . ')'])
                                        ->toArray();
                                })
                                ->required()
                                ->native(false)
                                ->live()
                                ->searchable(),

                            Forms\Components\Select::make('ruangan_id')
                                ->label('Ruangan')
                                ->options(function () {
                                    $sekolahId = auth()->user()->sekolah_id;
                                    return \App\Models\Ruangan::when($sekolahId, fn ($q) => $q->where('sekolah_id', $sekolahId))
                                        ->pluck('nama_ruangan', 'id')
                                        ->toArray();
                                })
                                ->required()
                                ->native(false)
                                ->searchable(),

                            Forms\Components\Select::make('kelas_ids')
                                ->label('Kelas/Rombel')
                                ->options(function (Get $get) {
                                    $jadwalId = $get('jadwal_tryout_id');
                                    $kelasOptions = [];
                                    if ($jadwalId) {
                                        $jadwal = \App\Models\JadwalTryout::with(['kelases', 'paketTryout'])->find($jadwalId);
                                        if ($jadwal) {
                                            $kelasOptions = $jadwal->kelases()->count() > 0
                                                ? $jadwal->kelases()->pluck('nama_kelas', 'kelas.id')->toArray()
                                                : \App\Models\Kelas::where('sekolah_id', auth()->user()->sekolah_id)
                                                    ->when($jadwal->paketTryout?->tingkat, fn ($q, $t) => $q->where('tingkat', $t))
                                                    ->pluck('nama_kelas', 'id')
                                                    ->toArray();
                                        }
                                    }
                                    return $kelasOptions;
                                })
                                ->multiple()
                                ->placeholder('Kosongkan = Semua Kelas')
                                ->helperText('Kosongkan untuk semua kelas, atau pilih satu/lebih kelas spesifik')
                                ->native(false)
                                ->searchable(),
                        ])
                        ->columns(3)
                        ->minItems(1)
                        ->addActionLabel('Tambah Penugasan')
                        ->columnSpanFull()
                        ->itemLabel(fn (array $state): ?string => 'Penugasan'),
                ])
                ->action(function (array $data) {
                    $proktorId = $data['proktor_id'];
                    $created = 0;
                    $skipped = 0;

                    foreach ($data['penugasan'] as $item) {
                        $jadwalId = $item['jadwal_tryout_id'] ?? null;
                        $ruanganId = $item['ruangan_id'] ?? null;
                        $kelasIds = $item['kelas_ids'] ?? []; // array bisa kosong = semua kelas

                        if (!$jadwalId || !$ruanganId) {
                            $skipped++;
                            continue;
                        }

                        // Jika tidak memilih kelas, buat 1 record dengan kelas null (Semua Kelas)
                        $targets = empty($kelasIds) ? [null] : $kelasIds;

                        foreach ($targets as $kelasId) {
                            // Cek duplikat: kelas yang sama di jadwal yang sama
                            $exists = JadwalRuanganProktor::where('jadwal_tryout_id', $jadwalId)
                                ->where('kelas_id', $kelasId)
                                ->exists();

                            if ($exists) {
                                $skipped++;
                                continue;
                            }

                            JadwalRuanganProktor::create([
                                'proktor_id' => $proktorId,
                                'jadwal_tryout_id' => $jadwalId,
                                'ruangan_id' => $ruanganId,
                                'kelas_id' => $kelasId,
                                'status' => 'active',
                            ]);

                            $created++;
                        }
                    }

                    if ($created > 0) {
                        Notification::make()
                            ->title("$created penugasan berhasil disimpan" . ($skipped > 0 ? ", $skipped dilewati (duplikat)." : '.'))
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Semua penugasan dilewati karena duplikat.')
                            ->warning()
                            ->send();
                    }
                }),
        ];
    }
}
