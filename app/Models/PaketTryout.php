<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketTryout extends Model
{
    use HasFactory;

    protected $table = 'paket_tryout';

    protected $fillable = [
        'sekolah_id',
        'nama_paket',
        'deskripsi',
        'jenjang',
        'total_waktu',
        'is_active',
    ];

    protected $casts = [
        'sekolah_id' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relasi ke sekolah
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    // Relasi ke mapel-mapel dalam paket
    public function mapelItems()
    {
        return $this->hasMany(PaketTryoutMapel::class, 'paket_tryout_id');
    }

    // Relasi ke jadwal
    public function jadwal()
    {
        return $this->hasMany(JadwalTryout::class, 'paket_tryout_id');
    }

    // Accessor: Total soal dari semua mapel
    public function getTotalSoalAttribute()
    {
        return $this->mapelItems->sum('jumlah_soal');
    }

    // Accessor: Total mapel dalam paket
    public function getJumlahMapelAttribute()
    {
        return $this->mapelItems->count();
    }
}
