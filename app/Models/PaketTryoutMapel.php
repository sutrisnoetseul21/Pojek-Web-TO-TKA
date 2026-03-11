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
    ];

    protected $casts = [
        'soal_ids' => 'array',
        'kategori_ids' => 'array',
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
        // Mode MANUAL: ambil soal sesuai ID yang dipilih
        if ($this->mode === 'MANUAL' && !empty($this->soal_ids)) {
            return BankSoal::with(['jawaban', 'stimulus'])->whereIn('id', $this->soal_ids)->get();
        }

        // Mode ACAK: ambil soal random dari kategori
        $query = BankSoal::with(['jawaban', 'stimulus'])
            ->whereIn('paket_id', $this->kategori_ids ?? [])
            ->where('mapel_id', $this->mapel_id);

        if ($randomize) {
            $query->inRandomOrder();
        }

        return $query->limit($this->jumlah_soal)->get();
    }
}
