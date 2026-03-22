<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ruangan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ruangan';

    protected $fillable = [
        'sekolah_id',
        'nama_ruangan',
        'kode_ruangan',
        'kapasitas',
        'keterangan',
    ];

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    public function pesertaJadwals()
    {
        return $this->hasMany(PesertaJadwal::class, 'ruangan_id');
    }

    public function jadwalProktors()
    {
        return $this->hasMany(JadwalRuanganProktor::class, 'ruangan_id');
    }
}
