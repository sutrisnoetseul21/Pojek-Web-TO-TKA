<?php

namespace App\Filament\Resources\PaketTryoutResource\Pages;

use App\Filament\Resources\PaketTryoutResource;
use App\Models\BankSoal;
use Filament\Resources\Pages\Page;
use Filament\Actions;

class LihatSoal extends Page
{
    protected static string $resource = PaketTryoutResource::class;

    protected static string $view = 'filament.resources.paket-tryout.lihat-soal';

    protected static ?string $title = 'Lihat Soal';

    public $record;

    public function mount($record): void
    {
        $this->record = \App\Models\PaketTryout::with(['mapelItems.mapel'])->findOrFail($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('kembali')
                ->label('← Kembali ke Daftar')
                ->url(PaketTryoutResource::getUrl('index'))
                ->color('gray'),
            Actions\Action::make('edit')
                ->label('Edit Paket')
                ->url(PaketTryoutResource::getUrl('edit', ['record' => $this->record]))
                ->color('warning'),
        ];
    }

    public function getSoalData(): array
    {
        $data = [];
        // $nomorGlobal = 1; // Jika ingin nomor continue antar mapel

        foreach ($this->record->mapelItems()->orderBy('urutan')->get() as $mapelItem) {
            $nomorGlobal = 1; // Reset nomor per mapel (biasanya tryout reset per mapel)

            // Ambil nama kategori
            $kategoriNames = '-';
            if (!empty($mapelItem->kategori_ids)) {
                $kategoriNames = \App\Models\RefPaketSoal::whereIn('id', $mapelItem->kategori_ids)
                    ->pluck('nama_paket')
                    ->join(', ');
            }

            $mapelData = [
                'nama_mapel' => $mapelItem->mapel->nama_mapel ?? '-',
                'kategori' => $kategoriNames,
                'mode' => $mapelItem->mode ?? 'ACAK',
                'waktu' => $mapelItem->waktu_mapel,
                'soal_list' => [],
                'jumlah' => 0
            ];

            // Gunakan method getSoal() dari model yang sudah diperbarui
            $soalItems = $mapelItem->getSoal(true); // true = randomize
            $mapelData['jumlah'] = $soalItems->count();

            foreach ($soalItems as $soal) {
                $mapelData['soal_list'][] = [
                    'nomor' => $nomorGlobal++,
                    'id' => $soal->id,
                    'tipe' => $soal->jenis_soal, // View pakai 'tipe'
                    'pertanyaan' => $soal->pertanyaan,
                    'bobot' => $soal->bobot ?? 1,
                    'jawaban' => $soal->jawaban->map(fn($j) => [
                        'teks' => $j->jawaban, // Di model BankJawaban kolomnya 'jawaban' bukan 'teks_jawaban' (cek schema/model jika ragu, tadi di dummy data pake 'jawaban')
                        'skor' => $j->is_benar ? ($soal->bobot ?? 1) : 0, // Logic sederhana skor
                        'kunci' => $j->is_benar ? 'BENAR' : 'SALAH',
                    ])->toArray(),
                ];
            }

            $data[] = $mapelData;
        }

        return $data;
    }
}
