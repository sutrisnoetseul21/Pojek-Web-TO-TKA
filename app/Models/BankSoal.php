<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankSoal extends Model
{
    use HasFactory;

    protected $table = 'bank_soal';

    protected $fillable = [
        'paket_id',
        'mapel_id',
        'stimulus_id',
        'tipe_soal',
        'pertanyaan',
        'pembahasan',
        'bobot',
        'nomor_urut',
    ];

    public function mapel()
    {
        return $this->belongsTo(RefMapel::class, 'mapel_id');
    }

    public function paket()
    {
        return $this->belongsTo(RefPaketSoal::class, 'paket_id');
    }

    public function stimulus()
    {
        return $this->belongsTo(BankStimulus::class, 'stimulus_id');
    }

    public function jawaban()
    {
        return $this->hasMany(BankJawaban::class, 'soal_id');
    }
}
