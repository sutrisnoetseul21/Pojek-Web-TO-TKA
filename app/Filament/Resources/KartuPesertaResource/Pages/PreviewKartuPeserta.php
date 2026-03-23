<?php

namespace App\Filament\Resources\KartuPesertaResource\Pages;

use App\Filament\Resources\KartuPesertaResource;
use Filament\Resources\Pages\Page;

class PreviewKartuPeserta extends Page
{
    protected static string $resource = KartuPesertaResource::class;

    protected static string $view = 'filament.resources.kartu-peserta-resource.pages.preview-kartu-peserta';

    public $users = [];
    public $filterLabel = '';
    public $namaSekolah = '';
    public $jenisCetak = 'tanpa_jadwal';

    public function mount()
    {
        $ids = request()->query('ids');
        $sekolahId = request()->query('sekolah_id');
        $kelasId = request()->query('kelas_id');
        $this->jenisCetak = request()->query('jenis_cetak', 'tanpa_jadwal');
        $jadwalTryoutId = request()->query('jadwal_tryout_id');

        $query = \App\Models\User::where('role', 'peserta')
            ->with([
                'sekolahRelation',
                'kelas',
                'jadwalTryouts' => function ($q) use ($jadwalTryoutId) {
                    if ($jadwalTryoutId) {
                        $q->where('jadwal_tryout.id', $jadwalTryoutId);
                    } else {
                        $q->latest('jadwal_tryout.created_at')->limit(1);
                    }
                },
            ])
            ->orderBy('username');

        if ($ids) {
            $idArray = explode(',', $ids);
            $query->whereIn('id', $idArray);
            $this->filterLabel = count($idArray) . ' Peserta Terpilih';
            // Ambil nama sekolah dari peserta pertama
            $firstUser = \App\Models\User::find($idArray[0]);
            if ($firstUser && $firstUser->sekolah_id) {
                $sekolah = \App\Models\Sekolah::find($firstUser->sekolah_id);
                $this->namaSekolah = $sekolah ? $sekolah->nama_sekolah : '—';
            }
        } else {
            if ($sekolahId) {
                $query->where('sekolah_id', $sekolahId);
                $sekolah = \App\Models\Sekolah::find($sekolahId);
                $this->filterLabel = $sekolah ? $sekolah->nama_sekolah : 'Sekolah Terpilih';
                $this->namaSekolah = $sekolah ? $sekolah->nama_sekolah : '—';

                if ($kelasId) {
                    $query->where('kelas_id', $kelasId);
                    $kelas = \App\Models\Kelas::find($kelasId);
                    if ($kelas) {
                        $this->filterLabel .= ' - ' . $kelas->nama_kelas;
                    }
                }
            } else {
                $this->filterLabel = 'Pilih filter untuk melihat preview';
            }
        }

        $this->users = $query->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('cetak')
                ->label('Cetak')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->extraAttributes(['onclick' => 'window.print()']),
            \Filament\Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->url(fn () => request()->fullUrl()),
        ];
    }
}
