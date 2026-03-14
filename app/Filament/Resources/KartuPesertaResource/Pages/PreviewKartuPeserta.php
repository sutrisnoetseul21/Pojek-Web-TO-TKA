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

    public function mount()
    {
        $ids = request()->query('ids');
        $sekolahId = request()->query('sekolah_id');
        $kelasId = request()->query('kelas_id');

        $query = \App\Models\User::where('role', 'peserta')
            ->with(['sekolahRelation', 'kelas'])
            ->orderBy('username');

        if ($ids) {
            $idArray = explode(',', $ids);
            $query->whereIn('id', $idArray);
            $this->filterLabel = count($idArray) . ' Peserta Terpilih';
        } else {
            if ($sekolahId) {
                $query->where('sekolah_id', $sekolahId);
                $sekolah = \App\Models\Sekolah::find($sekolahId);
                $this->filterLabel = $sekolah ? $sekolah->nama_sekolah : 'Sekolah Terpilih';

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
