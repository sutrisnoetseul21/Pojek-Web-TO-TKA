<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Sekolah;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KelasImport implements ToCollection, WithHeadingRow
{
    protected $sekolahId;

    public function __construct($sekolahId = null)
    {
        $this->sekolahId = $sekolahId;
    }

    public function collection(Collection $rows)
    {
        $sekolah = Sekolah::findOrFail($this->sekolahId);

        foreach ($rows as $index => $row) {
            $namaKelas = trim((string)($row['nama_kelas_wajib'] ?? ''));
            
            if (empty($namaKelas)) {
                continue;
            }

            // Validasi duplikasi di sekolah yang sama
            $exists = Kelas::where('sekolah_id', $this->sekolahId)
                ->where('nama_kelas', $namaKelas)
                ->exists();

            if ($exists) {
                throw new \Exception("Baris " . ($index + 2) . ": Kelas \"$namaKelas\" sudah ada di sekolah {$sekolah->nama_sekolah}.");
            }

            $jenjang = trim((string)($row['jenjang_opsional_kosongkan_untuk_ikut_sekolah'] ?? ''));
            if (empty($jenjang)) {
                $jenjang = $sekolah->jenjang;
            }

            $keterangan = $row['keterangan'] ?? null;

            Kelas::create([
                'sekolah_id' => $this->sekolahId,
                'nama_kelas' => $namaKelas,
                'jenjang'    => $jenjang,
                'keterangan' => $keterangan,
            ]);
        }
    }
}
