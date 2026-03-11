<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaJadwal extends Model
{
    use HasFactory;

    protected $table = 'peserta_jadwal';

    protected $fillable = [
        'user_id',
        'jadwal_tryout_id',
        'token_used',
        'status',
        'waktu_mulai',
        'waktu_selesai',
        'sisa_waktu',
        'total_nilai',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    // Relasi ke user/peserta
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke jadwal
    public function jadwalTryout()
    {
        return $this->belongsTo(JadwalTryout::class);
    }

    // Scope: yang sudah selesai
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Scope: yang sedang berlangsung
    public function scopeStarted($query)
    {
        return $query->where('status', 'started');
    }
}
