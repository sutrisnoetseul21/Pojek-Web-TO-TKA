<?php

namespace App\Imports;

use App\Models\User;
use App\Filament\Resources\UserResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserImport implements ToCollection, WithHeadingRow
{
    protected $sekolahId;
    protected $kelasId;

    public function __construct($sekolahId = null, $kelasId = null)
    {
        $this->sekolahId = $sekolahId;
        $this->kelasId = $kelasId;
    }

    public function collection(Collection $rows)
    {
        // Get the active prefix configured by admin, default to P + current year 
        $prefix = Cache::get('user_username_prefix', 'P' . date('Y') . '00');
        
        $currentNumber = UserResource::getNextUsernameNumberFromPrefix($prefix);

        $sekolah = \App\Models\Sekolah::find($this->sekolahId);
        $sekolahNama = $sekolah ? $sekolah->nama_sekolah : 'Sekolah';

        foreach ($rows as $index => $row) {
            $namaLengkap = $row['nama_lengkap_wajib'] ?? null;
            
            if (empty($namaLengkap)) {
                continue;
            }

            $jkInput = strtoupper(trim((string)($row['jenis_kelamin_lp_opsional'] ?? '')));
            $jenisKelamin = in_array($jkInput, ['L', 'P']) ? $jkInput : null;

            $kelasNama = trim((string)($row['kelas_wajib_jika_tidak_pilih_saat_download'] ?? ''));
            $finalKelasId = $this->kelasId;

            if (!empty($kelasNama)) {
                $kelas = \App\Models\Kelas::where('sekolah_id', $this->sekolahId)
                    ->where('nama_kelas', $kelasNama)
                    ->first();

                if (!$kelas) {
                    throw new \Exception("Baris " . ($index + 2) . ": Kelas \"$kelasNama\" tidak ditemukan di sekolah $sekolahNama");
                }
                $finalKelasId = $kelas->id;
            }

            if (empty($finalKelasId)) {
                throw new \Exception("Baris " . ($index + 2) . ": Kelas wajib diisi jika tidak memilih kelas saat melakukan import/download template.");
            }

            $username = trim((string)($row['username_opsional_kosongkan_untuk_otomatis'] ?? ''));
            if (empty($username)) {
                $username = $prefix . $currentNumber;
                $currentNumber++;
            }

            $password = trim((string)($row['password_opsional_kosongkan_untuk_otomatis'] ?? ''));
            if (empty($password)) {
                $password = UserResource::generatePassword();
            }

            // Create user
            User::create([
                'name'                => $namaLengkap,
                'nama_lengkap'        => $namaLengkap,
                'username'            => $username,
                'email'               => $username . '@tryout.local',
                'password'            => Hash::make($password),
                'plain_password'      => $password,
                'role'                => 'peserta',
                'sekolah_id'          => $this->sekolahId,
                'kelas_id'            => $finalKelasId,
                'jenjang'             => $sekolah->jenjang ?? null,
                'jenis_kelamin'       => $jenisKelamin,
                'is_biodata_complete' => false,
            ]);
        }
    }
}
