<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankJawaban extends Model
{
    use HasFactory;

    protected $table = 'bank_jawaban';

    protected $fillable = [
        'soal_id',
        'teks_jawaban',
        'kunci_jawaban',
        'label',
        'skor',
    ];

    public function soal()
    {
        return $this->belongsTo(BankSoal::class, 'soal_id');
    }
}
