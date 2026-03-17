<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketTryoutMapel extends Model
{
    use HasFactory;

    protected $table = 'paket_tryout_mapel';

    protected $fillable = [
        'paket_tryout_id',
        'mapel_id',
        'kategori_ids',
        'mode',
        'soal_ids',
        'jumlah_soal',
        'waktu_mapel',
        'urutan',
        'kategori_settings',
    ];

    protected $casts = [
        'soal_ids' => 'array',
        'kategori_ids' => 'array',
        'kategori_settings' => 'array',
    ];

    // Relasi ke paket tryout
    public function paketTryout()
    {
        return $this->belongsTo(PaketTryout::class, 'paket_tryout_id');
    }

    // Relasi ke mata pelajaran
    public function mapel()
    {
        return $this->belongsTo(RefMapel::class, 'mapel_id');
    }

    // Method: Ambil soal dari kategori ini
    public function getSoal($randomize = true)
    {
        // 1. Logika Baru: Menggunakan pengaturan per Kategori
        if (!empty($this->kategori_settings)) {
            $allSoal = collect();

            foreach ($this->kategori_settings as $setting) {
                $katId = $setting['kategori_id'] ?? null;
                $mode = $setting['mode'] ?? 'ACAK';
                $jumlah = $setting['jumlah_soal'] ?? 10;
                $soalIds = $setting['soal_ids'] ?? [];

                if (!$katId) continue;

                $query = BankSoal::with(['jawaban', 'stimulus'])
                    ->where('paket_id', $katId) // paket_id di BankSoal mewakili Kategori ID
                    ->where('mapel_id', $this->mapel_id);

                if ($mode === 'MANUAL' && !empty($soalIds)) {
                    $query->whereIn('id', $soalIds);
                    $allSoal = $allSoal->merge($query->get());
                } elseif ($mode === 'SEMUA') {
                    $allSoal = $allSoal->merge($query->get());
                } else { // Mode ACAK
                    if ($randomize) {
                        $query->inRandomOrder();
                    }
                    $allSoal = $allSoal->merge($query->limit($jumlah)->get());
                }
            }

            return $allSoal;
        }

        // 2. Berfungsi sebagai Fallback Struktur Lama (Kompatibilitas Mundur)
        if ($this->mode === 'MANUAL' && !empty($this->soal_ids)) {
            return BankSoal::with(['jawaban', 'stimulus'])->whereIn('id', $this->soal_ids)->get();
        }

        $query = BankSoal::with(['jawaban', 'stimulus'])
            ->whereIn('paket_id', $this->kategori_ids ?? [])
            ->where('mapel_id', $this->mapel_id);

        if ($randomize) {
            $query->inRandomOrder();
        }

        return $query->limit($this->jumlah_soal)->get();
    }
}
