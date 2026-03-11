<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanPeserta extends Model
{
    protected $table = 'jawaban_peserta';

    protected $fillable = [
        'peserta_jadwal_id',
        'bank_soal_id',
        'jawaban',
        'is_ragu',
    ];

    protected $casts = [
        'jawaban' => 'array',
        'is_ragu' => 'boolean',
    ];

    /**
     * Relasi ke PesertaJadwal
     */
    public function pesertaJadwal(): BelongsTo
    {
        return $this->belongsTo(PesertaJadwal::class);
    }

    /**
     * Relasi ke BankSoal
     */
    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(BankSoal::class);
    }
}
