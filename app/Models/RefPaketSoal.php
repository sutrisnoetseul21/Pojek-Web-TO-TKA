<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefPaketSoal extends Model
{
    use HasFactory;

    protected $table = 'ref_paket_soal';

    protected $fillable = [
        'nama_paket',
        'jenjang',
        'mapel_id',
        'keterangan',
    ];

    public function mapel()
    {
        return $this->belongsTo(RefMapel::class, 'mapel_id');
    }

    public function stimulus()
    {
        return $this->hasMany(BankStimulus::class, 'paket_id');
    }

    public function soal()
    {
        return $this->hasMany(BankSoal::class, 'paket_id');
    }
}
