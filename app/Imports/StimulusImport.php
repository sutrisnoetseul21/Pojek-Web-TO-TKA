<?php

namespace App\Imports;

use App\Models\BankStimulus;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class StimulusImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Skip if required fields are missing
        if (empty($row['mapel_pilih']) || empty($row['judul_stimulus'])) {
            return null;
        }

        // Ekstrak ID dari format "ID - Nama - Jenjang" atau "ID - Nama"
        // Contoh: "1 - Matematika - SD" -> 1
        $mapelId = (int) Str::before($row['mapel_pilih'], ' -');
        
        $paketId = null;
        if (!empty($row['paket_pilih_opsional'])) {
            $paketId = (int) Str::before($row['paket_pilih_opsional'], ' -');
        }

        return new BankStimulus([
            'mapel_id'    => $mapelId,
            'paket_id'    => $paketId,
            'judul'       => $row['judul_stimulus'],
            'tipe'        => strtoupper($row['tipe_teksgambar'] ?? 'TEKS'),
            'konten'      => $row['konten_wacana_html'] ?? '',
        ]);
    }
}
