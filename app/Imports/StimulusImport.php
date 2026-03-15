<?php

namespace App\Imports;

use App\Models\BankStimulus;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class StimulusImport implements ToModel, WithHeadingRow
{
    protected $jenjang;

    public function __construct($jenjang = null)
    {
        $this->jenjang = $jenjang;
    }

    public function model(array $row)
    {
        // Skip if required fields are missing
        if (empty($row['mapel_pilih']) || empty($row['judul_stimulus'])) {
            return null;
        }

        // Ekstrak ID dari format "ID - Nama - Jenjang" atau "ID - Nama"
        // Contoh: "1 - Matematika - SD" -> 1
        $mapelId = (int) Str::before($row['mapel_pilih'], ' -');
        
        // Validasi Jenjang
        if ($this->jenjang) {
            $mapel = \App\Models\RefMapel::find($mapelId);
            if ($mapel && $mapel->jenjang !== $this->jenjang) {
                // Skip atau throw. Mengingat ToModel bisa return null untuk skip, kita return null saja jika tidak cocok
                // Tapi lebih baik melempar pesan agar user tahu
                throw new \Exception("Mata Pelajaran \"{$mapel->nama_mapel}\" ({$mapel->jenjang}) tidak sesuai dengan jenjang Anda ({$this->jenjang}).");
            }
        }

        $paketId = null;
        if (!empty($row['paket_pilih_opsional'])) {
            $paketId = (int) Str::before($row['paket_pilih_opsional'], ' -');
            
            // Validasi Paket milik Mapel
            if ($paketId) {
                $paket = \App\Models\RefPaketSoal::find($paketId);
                if ($paket && $paket->mapel_id != $mapelId) {
                    throw new \Exception("Paket \"{$paket->nama_paket}\" tidak termasuk dalam Mata Pelajaran ini.");
                }
            }
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
