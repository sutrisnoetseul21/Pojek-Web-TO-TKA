<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalTryout extends Model
{
    use HasFactory;

    protected $table = 'jadwal_tryout';

    protected $fillable = [
        'paket_tryout_id',
        'nama_sesi',
        'tgl_mulai',
        'tgl_selesai',
        'kuota_peserta',
        'is_active',
        'token',
    ];

    protected $casts = [
        'tgl_mulai' => 'datetime',
        'tgl_selesai' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Boot method untuk auto-generate token
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($jadwal) {
            if (!$jadwal->token) {
                $jadwal->token = strtoupper(substr(md5(uniqid()), 0, 6));
            }
        });
    }

    // Relasi ke paket tryout
    public function paketTryout()
    {
        return $this->belongsTo(PaketTryout::class, 'paket_tryout_id');
    }

    // Relasi ke peserta yang terdaftar
    public function peserta()
    {
        return $this->belongsToMany(User::class, 'peserta_jadwal')
            ->withPivot(['token_used', 'status', 'waktu_mulai', 'waktu_selesai', 'sisa_waktu', 'total_nilai'])
            ->withTimestamps();
    }

    // Scope: Jadwal yang aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: Jadwal yang akan datang
    public function scopeUpcoming($query)
    {
        return $query->where('tgl_mulai', '>', now());
    }

    // Scope: Jadwal yang sedang berjalan
    public function scopeOngoing($query)
    {
        return $query->where('tgl_mulai', '<=', now())
            ->where('tgl_selesai', '>=', now());
    }

    // Accessor: Status jadwal
    public function getStatusAttribute()
    {
        $now = now();

        if ($now < $this->tgl_mulai) {
            return 'AKAN_DATANG';
        } elseif ($now >= $this->tgl_mulai && $now <= $this->tgl_selesai) {
            return 'BERLANGSUNG';
        } else {
            return 'SELESAI';
        }
    }
}
