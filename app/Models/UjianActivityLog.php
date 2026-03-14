<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UjianActivityLog extends Model
{
    protected $table = 'ujian_activity_log';

    protected $fillable = [
        'peserta_jadwal_id',
        'user_id',
        'jadwal_tryout_id',
        'aktivitas',
        'keterangan',
        'ip_address',
    ];

    public function pesertaJadwal(): BelongsTo
    {
        return $this->belongsTo(PesertaJadwal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jadwalTryout(): BelongsTo
    {
        return $this->belongsTo(JadwalTryout::class);
    }
}
