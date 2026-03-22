<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalRuanganProktor extends Model
{
    use HasFactory;

    protected $table = 'jadwal_ruangan_proktor';

    protected $fillable = [
        'jadwal_tryout_id',
        'ruangan_id',
        'proktor_id',
        'kelas_id',
        'status',
        'catatan',
    ];

    public function jadwalTryout()
    {
        return $this->belongsTo(JadwalTryout::class);
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function proktor()
    {
        return $this->belongsTo(User::class, 'proktor_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
