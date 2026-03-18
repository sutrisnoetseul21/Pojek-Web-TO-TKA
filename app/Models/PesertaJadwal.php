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
        'current_mapel_id',
        'token_used',
        'status',
        'waktu_mulai',
        'waktu_selesai',
        'sisa_waktu',
        'total_nilai',
        'request_reset_at',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    /**
     * Kalkulasi nilai total dan submisi ke status completed (atau lanjut mapel)
     */
    public function calculateAndSubmit($isFinalSubmit = true)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($isFinalSubmit) {
            // 1. Hitung Nilai
            $jawaban = \App\Models\JawabanPeserta::where('peserta_jadwal_id', $this->id)
                ->with('bankSoal')
                ->get();

            $totalNilai = 0;
            foreach ($jawaban as $j) {
                $soal = $j->bankSoal;
                if (!$soal) continue;

                $userJawaban = is_string($j->jawaban) ? json_decode($j->jawaban, true) : $j->jawaban;

                if ($soal->tipe_soal === 'PG_TUNGGAL' || $soal->tipe_soal === 'PG') {
                    $opsi = $soal->jawaban->where('id', $userJawaban)->first();
                    if ($opsi) $totalNilai += $opsi->skor ?? 0;
                } elseif ($soal->tipe_soal === 'PG_KOMPLEKS') {
                    if (is_array($userJawaban)) {
                        $skorKasar = $soal->jawaban->whereIn('id', $userJawaban)->sum('skor');
                        $totalNilai += max(0, $skorKasar);
                    }
                } elseif ($soal->tipe_soal === 'BENAR_SALAH') {
                    if (is_array($userJawaban)) {
                        foreach ($soal->jawaban as $opsi) {
                            $jawabanUser = $userJawaban[$opsi->id] ?? null;
                            if ($jawabanUser) {
                                $jawabanUser = strtoupper($jawabanUser);
                                $kunci = strtoupper($opsi->kunci_jawaban ?? '');
                                
                                if ($kunci && $jawabanUser === $kunci) {
                                    $totalNilai += $opsi->skor ?? 0;
                                } elseif (!$kunci && $opsi->skor > 0 && $jawabanUser === 'BENAR') {
                                    $totalNilai += $opsi->skor;
                                }
                            }
                        }
                    }
                }
            }

            // Fallback Logic untuk "Lanjut Mapel"
            if (!$isFinalSubmit) {
                $paket = $this->jadwalTryout?->paketTryout;
                if ($paket) {
                    // Cari mapel saat ini berdasarkan urutan
                    $currentMapelItem = $paket->mapelItems()
                        ->where('mapel_id', $this->current_mapel_id)
                        ->first();

                    // Cari mapel berikutnya
                    $nextMapelItem = $paket->mapelItems()
                        ->where('urutan', '>', $currentMapelItem?->urutan ?? 0)
                        ->orderBy('urutan')
                        ->first();

                    if ($nextMapelItem) {
                        // Jika ada mapel selanjutnya: Set current_mapel_id, Reset Timer
                        $this->update([
                            'current_mapel_id' => $nextMapelItem->mapel_id,
                            'sisa_waktu' => $nextMapelItem->waktu_mapel, // Ambil alokasi waktu mapel baru
                            'waktu_mulai' => now(), // reset timer
                            'total_nilai' => $totalNilai,
                        ]);
                        return true;
                    } else {
                        // Jika sudah mapel terakhir, paksa final submit
                        $isFinalSubmit = true;
                    }
                }
            }

            // Final Submit (Selesai Total)
            $this->update([
                'status' => 'completed',
                'waktu_selesai' => now(),
                'total_nilai' => $totalNilai,
            ]);

            return true;
        });
    }

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

    // Relasi ke Mapel Saat Ini (Subtes)
    public function currentMapel()
    {
        return $this->belongsTo(RefMapel::class, 'current_mapel_id');
    }

    // Relasi ke jawaban peserta
    public function jawabanPeserta()
    {
        return $this->hasMany(JawabanPeserta::class);
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
