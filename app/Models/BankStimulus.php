<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankStimulus extends Model
{
    use HasFactory;

    protected $table = 'bank_stimulus';

    protected $fillable = [
        'mapel_id',
        'paket_id',
        'judul',
        'konten',
        'tipe',
        'file_path',
    ];

    public function mapel()
    {
        return $this->belongsTo(RefMapel::class, 'mapel_id');
    }

    public function paket()
    {
        return $this->belongsTo(RefPaketSoal::class, 'paket_id');
    }

    public function soal()
    {
        return $this->hasMany(BankSoal::class, 'stimulus_id');
    }
}
