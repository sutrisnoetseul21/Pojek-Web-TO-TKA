<?php

namespace App\Imports;

use App\Models\RefMapel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RefMapelImport implements ToModel, WithHeadingRow
{
    protected $jenjang;

    public function __construct($jenjang = null)
    {
        $this->jenjang = $jenjang;
    }

    public function model(array $row)
    {
        if (empty($row['nama_mapel']) || empty($row['kode_mapel'])) {
            return null;
        }

        $rowJenjang = strtoupper(trim($row['jenjang_sdsmp_smasmkumum'] ?? ''));
        
        if ($this->jenjang) {
            // Admin: override or enforce match
            if (!empty($rowJenjang) && $rowJenjang !== $this->jenjang) {
                 throw new \Exception("Jenjang \"{$rowJenjang}\" tidak sesuai dengan jenjang Anda ({$this->jenjang}).");
            }
            $rowJenjang = $this->jenjang; // Safe override
        }

        if (empty($rowJenjang)) {
            $rowJenjang = 'UMUM';
        }

        // Cek duplikasi kode_mapel & jenjang
        $exists = RefMapel::where('kode_mapel', $row['kode_mapel'])
            ->where('jenjang', $rowJenjang)
            ->exists();

        if ($exists) {
            return null; // Skip duplicate
        }

        return new RefMapel([
            'nama_mapel' => $row['nama_mapel'],
            'kode_mapel' => $row['kode_mapel'],
            'jenjang'    => $rowJenjang,
        ]);
    }
}
