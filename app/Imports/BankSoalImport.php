<?php

namespace App\Imports;

use App\Models\BankSoal;
use App\Models\BankJawaban;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class BankSoalImport implements ToModel, WithHeadingRow
{
    protected $jenjang;

    public function __construct($jenjang = null)
    {
        $this->jenjang = $jenjang;
    }

    public function model(array $row)
    {
        // Skip if required fields are missing
        if (empty($row['mapel_pilih']) || empty($row['kategori_paket_pilih_opsional']) || empty($row['pertanyaan'])) {
            return null;
        }

        // Ekstrak ID (Format "ID - Nama")
        $mapelId = (int) Str::before($row['mapel_pilih'], ' -');
        
        if ($this->jenjang) {
            $mapel = \App\Models\RefMapel::find($mapelId);
            if ($mapel && $mapel->jenjang !== $this->jenjang) {
                throw new \Exception("Mata Pelajaran \"{$mapel->nama_mapel}\" ({$mapel->jenjang}) tidak sesuai dengan jenjang Anda ({$this->jenjang}).");
            }
        }

        $paketId = (int) Str::before($row['kategori_paket_pilih_opsional'], ' -');
        
        // Validasi Paket milik Mapel
        if ($paketId) {
            $paket = \App\Models\RefPaketSoal::find($paketId);
            if ($paket && $paket->mapel_id != $mapelId) {
                throw new \Exception("Kategori / Paket \"{$paket->nama_paket}\" tidak termasuk dalam Mata Pelajaran ini.");
            }
        }

        $stimulusId = null;
        if (!empty($row['stimulus_pilih_opsional'])) {
            $stimulusId = (int) Str::before($row['stimulus_pilih_opsional'], ' -');
        }

        $tipeSoal = strtoupper($row['tipe_soal_pilih'] ?? 'PG_TUNGGAL');
        
        // Cek model Benar_Salah, jika ter-input dengan spasi atau beda format
        if (Str::contains($tipeSoal, 'BENAR') && Str::contains($tipeSoal, 'SALAH')) {
            $tipeSoal = 'BENAR_SALAH';
        }

        // Buat record BankSoal
        $soal = BankSoal::create([
            'mapel_id'    => $mapelId,
            'paket_id'    => $paketId,
            'stimulus_id' => $stimulusId,
            'tingkat'     => $row['tingkat'] ?? null,
            'tipe_soal'   => $tipeSoal,
            'pertanyaan'  => $row['pertanyaan'] ?? '',
            'pembahasan'  => $row['pembahasan'] ?? null,
            'bobot'       => floatval($row['bobot_soal_angka'] ?? 1),
            'nomor_urut'  => 0, // Default 0
        ]);

        // Proses 10 Kolom Opsi Dinamis
        for ($i = 1; $i <= 10; $i++) {
            // Header excel biasanya di-slugify oleh WithHeadingRow (huruf kecil, spasi jadi underscore)
            // Header asli: "TEKS OPSI 1 / PERNYATAAN" -> "teks_opsi_1_pernyataan"
            // Header asli: "KUNCI/SKOR OPSI 1 (ANGKA/TEKS)" -> "kunciskor_opsi_1_angkateks"
            
            $teksKey  = "teks_opsi_" . $i . "_pernyataan";
            $kunciKey = "kunciskor_opsi_" . $i . "_angkateks";

            // Jika teks opsi kosong, berarti tidak ada opsi lebih lanjut di susunan baris ini
            if (empty($row[$teksKey])) {
                continue; // skip ke iterasi berikutnya, siapa tahu diisi longkap (walau seharusnya tidak)
            }

            $teksOpsi = $row[$teksKey];
            $kunciSkorInput = $row[$kunciKey] ?? '';

            $skor = 0;
            $kunciJawaban = null;

            if ($tipeSoal === 'BENAR_SALAH') {
                // Konversi teks (BENAR/SALAH) ke kunci jawaban
                $kunciTeks = strtoupper(trim((string)$kunciSkorInput));
                if (in_array($kunciTeks, ['BENAR', 'SALAH'])) {
                    $kunciJawaban = $kunciTeks;
                    $skor = ($kunciTeks === 'BENAR') ? 1 : 0; // Default logic
                }
            } else {
                // Untuk PG dan PG Kompleks, asumsi input adalah angka
                $skor = floatval($kunciSkorInput);
            }

            // Simpan Opsi Jawaban
            BankJawaban::create([
                'soal_id'       => $soal->id,
                'teks_jawaban'  => $teksOpsi,
                'kunci_jawaban' => $kunciJawaban,
                'skor'          => $skor,
                'label'         => null, // Bisa dipakai untuk A,B,C,D jika diperlukan
            ]);
        }

        return $soal;
    }
}
