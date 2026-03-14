<?php

namespace App\Imports;

use App\Models\Sekolah;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SekolahImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $namaSekolah = trim((string)($row['nama_sekolah_wajib'] ?? ''));
            $npsn = trim((string)($row['npsn_wajib_8_digit'] ?? ''));
            $jenjang = trim((string)($row['jenjang_wajib'] ?? ''));
            
            if (empty($namaSekolah) || empty($npsn) || empty($jenjang)) {
                continue;
            }

            // Validasi NPSN length
            if (strlen($npsn) !== 8) {
                throw new \Exception("Baris " . ($index + 2) . ": NPSN harus 8 digit.");
            }

            // Validasi NPSN uniqueness
            $exists = Sekolah::where('npsn', $npsn)->exists();
            if ($exists) {
                throw new \Exception("Baris " . ($index + 2) . ": Sekolah dengan NPSN \"$npsn\" sudah ada.");
            }

            $alamat = $row['alamat'] ?? null;

            Sekolah::create([
                'nama_sekolah' => $namaSekolah,
                'npsn'         => $npsn,
                'jenjang'      => $jenjang,
                'alamat'       => $alamat,
            ]);
        }
    }
}
