<?php

namespace App\Imports;

use App\Models\RefPaketSoal;
use App\Models\RefMapel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RefPaketSoalImport implements ToCollection, WithHeadingRow
{
    protected $jenjang;

    public function __construct($jenjang = null)
    {
        $this->jenjang = $jenjang;
    }

    public function collection(Collection $rows)
    {
        $user = auth()->user();

        foreach ($rows as $index => $row) {
            $namaPaket = trim((string)($row['nama_paket'] ?? ''));
            $mapelStr = trim((string)($row['mata_pelajaran'] ?? ''));
            $keterangan = $row['keterangan'] ?? null;

            if (empty($namaPaket) || empty($mapelStr)) {
                continue;
            }

            // Parse ID dari Mata Pelajaran (Format: "12 - Matematika - SMA")
            $mapelId = null;
            if (preg_match('/^(\d+)\s*-\s*/', $mapelStr, $matches)) {
                $mapelId = $matches[1];
            } else {
                // Fallback pencarian nama jika user ketik manual
                $mapelNodes = explode(' - ', $mapelStr);
                $namaMapel = $mapelNodes[0] ?? '';
                
                $query = RefMapel::where('nama_mapel', 'like', "%$namaMapel%");
                if ($this->jenjang) {
                    $query->where('jenjang', $this->jenjang);
                }
                $mapel = $query->first();

                if ($mapel) {
                    $mapelId = $mapel->id;
                }
            }

            if (!$mapelId) {
                throw new \Exception("Baris " . ($index + 2) . ": Mata Pelajaran \"$mapelStr\" tidak ditemukan.");
            }

            // Validasi Mapel (Opsional: pastikan sesuai jenjang admin)
            $mapel = RefMapel::find($mapelId);
            if ($mapel && $this->jenjang && $mapel->jenjang !== $this->jenjang) {
                throw new \Exception("Baris " . ($index + 2) . ": Mata Pelajaran \"{$mapel->nama_mapel}\" ({$mapel->jenjang}) tidak sesuai dengan jenjang Anda ({$this->jenjang}).");
            }

            // Validasi Duplikasi dalam Mapel
            $exists = RefPaketSoal::where('mapel_id', $mapelId)
                ->where('nama_paket', $namaPaket)
                ->exists();

            if ($exists) {
                // Skip atau throw? Biasanya throw untuk data integritas, tapi optional
                // throw new \Exception("Baris " . ($index + 2) . ": Paket \"$namaPaket\" sudah ada untuk Mapel \"{$mapel->nama_mapel}\".");
                continue;
            }

            $tingkat = $row['tingkat'] ?? null;

            RefPaketSoal::create([
                'nama_paket' => $namaPaket,
                'mapel_id'   => $mapelId,
                'jenjang'    => $mapel->jenjang, // Auto ikut mapel
                'tingkat'    => $tingkat,
                'keterangan' => $keterangan,
            ]);
        }
    }
}
