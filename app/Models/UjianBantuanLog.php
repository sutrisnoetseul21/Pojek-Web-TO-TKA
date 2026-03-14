<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UjianBantuanLog extends Model
{
    protected $table = 'ujian_bantuan_log';

    protected $fillable = [
        'peserta_jadwal_id',
        'admin_user_id',
        'tindakan',
        'keterangan',
    ];

    public function pesertaJadwal(): BelongsTo
    {
        return $this->belongsTo(PesertaJadwal::class);
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
