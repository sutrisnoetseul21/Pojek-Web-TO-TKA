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
        'sekolah_id',
        'nama_sesi',
        'tgl_mulai',
        'tgl_selesai',
        'kuota_peserta',
        'is_active',
        'token',
    ];

    protected $casts = [
        'paket_tryout_id' => 'integer',
        'sekolah_id' => 'integer',
        'tgl_mulai' => 'datetime',
        'tgl_selesai' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Boot method untuk auto-generate token & auto-sync peserta
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($jadwal) {
            if (!$jadwal->token) {
                $jadwal->token = strtoupper(substr(md5(uniqid()), 0, 6));
            }
        });

        // Sinkronisasi peserta saat jadwal disimpan (setelah pivot kelases disimpan)
        // Note: Karena Filament menyimpan relationship setelan model disimpan, 
        // kita perlu menggunakan hook model atau overriding form save di resource.
        // Cara paling aman di model level adalah menggunakan event 'saved'.
        static::saved(function ($jadwal) {
            // Ambil semua user_id yang ada di kelas-kelas yang dipilih
            $kelasIds = $jadwal->kelases()->pluck('kelas.id');
            
            if ($kelasIds->isNotEmpty()) {
                $userIds = \App\Models\User::whereIn('kelas_id', $kelasIds)
                    ->where('role', 'peserta')
                    ->pluck('id')
                    ->toArray();

                // Daftarkan ke peserta_jadwal jika belum ada
                foreach ($userIds as $userId) {
                    \App\Models\PesertaJadwal::firstOrCreate([
                        'user_id' => $userId,
                        'jadwal_tryout_id' => $jadwal->id,
                    ], [
                        'token_used' => $jadwal->token,
                        'status' => 'registered',
                    ]);
                }
            }
        });
    }

    // Relasi ke paket tryout
    public function paketTryout()
    {
        return $this->belongsTo(PaketTryout::class, 'paket_tryout_id');
    }

    // Relasi ke sekolah
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    // Relasi ke kelas yang ditargetkan
    public function kelases()
    {
        return $this->belongsToMany(Kelas::class, 'jadwal_tryout_kelas', 'jadwal_tryout_id', 'kelas_id');
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
