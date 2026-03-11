<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefMapel extends Model
{
    use HasFactory;

    protected $table = 'ref_mapel';

    protected $fillable = [
        'nama_mapel',
        'kode_mapel',
        'jenjang',
    ];

    public function stimulus()
    {
        return $this->hasMany(BankStimulus::class, 'mapel_id');
    }

    public function soal()
    {
        return $this->hasMany(BankSoal::class, 'mapel_id');
    }
}
