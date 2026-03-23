<?php

namespace App\Filament\Resources\KartuPesertaResource\Pages;

use App\Filament\Resources\KartuPesertaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Forms;
use App\Models\JadwalTryout;
use App\Filament\Resources\KartuPesertaResource\Pages\PreviewKartuPeserta;

class ManageKartuPesertas extends ManageRecords
{
    protected static string $resource = KartuPesertaResource::class;

    public function mount(): void
    {
        parent::mount();
        $this->mountAction('cetak_masal');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cetak_masal')
                ->label('Cetak Masal')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->form([
                    Forms\Components\Select::make('jenis_cetak')
                        ->label('Jenis Cetakan')
                        ->options([
                            'tanpa_jadwal' => '1. Cetak hanya kartu login tanpa jadwal ujian',
                            'dengan_jadwal' => '2. Cetak kartu peserta dengan jadwal ujian',
                        ])
                        ->required()
                        ->default('tanpa_jadwal')
                        ->live(),
                    Forms\Components\Select::make('jadwal_tryout_id')
                        ->label('Jadwal Ujian')
                        ->options(function () {
                            return JadwalTryout::orderBy('tgl_mulai', 'desc')
                                ->with('paketTryout')
                                ->get()
                            ->mapWithKeys(function ($jadwal) {
                                $namaUjian = $jadwal->paketTryout ? $jadwal->paketTryout->nama_paket : '—';
                                $sesi = $jadwal->nama_sesi ? " - Sesi: {$jadwal->nama_sesi}" : '';
                                return [$jadwal->id => "{$namaUjian}{$sesi}"];
                            });
                        })
                        ->required(fn (Forms\Get $get) => $get('jenis_cetak') === 'dengan_jadwal')
                        ->visible(fn (Forms\Get $get) => $get('jenis_cetak') === 'dengan_jadwal')
                        ->searchable()
                        ->preload()
                        ->live(),
                    Forms\Components\Select::make('sekolah_id')
                        ->label('Sekolah')
                        ->options(fn() => \App\Models\Sekolah::pluck('nama_sekolah', 'id'))
                        ->default(fn () => auth()->user()->sekolah_id)
                        ->disabled(fn () => auth()->user()->isAdmin())
                        ->dehydrated()
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live(),
                    Forms\Components\Select::make('kelas_id')
                        ->label('Kelas')
                        ->options(function (Forms\Get $get) {
                            $sekolahId = $get('sekolah_id');
                            $jenisCetak = $get('jenis_cetak');
                            $jadwalId = $get('jadwal_tryout_id');

                            $query = \App\Models\Kelas::query();

                            if ($sekolahId) {
                                $query->where('sekolah_id', $sekolahId);
                            }

                            if ($jenisCetak === 'dengan_jadwal' && $jadwalId) {
                                $jadwal = JadwalTryout::find($jadwalId);
                                if ($jadwal) {
                                    $kelasIds = $jadwal->kelases()->pluck('kelas.id')->toArray();
                                    $query->whereIn('id', $kelasIds);
                                }
                            }

                            return $query->pluck('nama_kelas', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Kelas di Sekolah Ini')
                        ->helperText('Kosongkan untuk mencetak semua kelas di sekolah terpilih'),
                ])
                ->action(function (array $data) {
                    return redirect()->to(PreviewKartuPeserta::getUrl([
                        'jenis_cetak'      => $data['jenis_cetak'],
                        'jadwal_tryout_id' => $data['jadwal_tryout_id'] ?? null,
                        'sekolah_id'       => $data['sekolah_id'],
                        'kelas_id'         => $data['kelas_id'] ?: null,
                    ]));
                })
                ->visible(fn () => auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()),
        ];
    }
}
