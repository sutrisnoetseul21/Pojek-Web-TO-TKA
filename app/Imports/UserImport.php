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
    public function collection(Collection $rows)
    {
        // Get the active prefix configured by admin, default to P + current year 
        // e.g., P202600
        $prefix = Cache::get('user_username_prefix', 'P' . date('Y') . '00');
        
        // Find the next available number sequence for this prefix based on current DB state
        // We initialize this and manually increment it per loop to avoid hitting DB inside loop repeatedly
        $currentNumber = UserResource::getNextUsernameNumberFromPrefix($prefix);

        foreach ($rows as $row) {
            // Excel heading row format: "NAMA LENGKAP (WAJIB)" becomes "nama_lengkap_wajib"
            $namaLengkap = $row['nama_lengkap_wajib'] ?? null;
            
            // Skip invalid rows unconditionally
            if (empty($namaLengkap)) {
                continue;
            }

            $sekolah = $row['sekolah_opsional'] ?? null;
            
            $jkInput = strtoupper(trim((string)($row['jenis_kelamin_lp_opsional'] ?? '')));
            $jenisKelamin = in_array($jkInput, ['L', 'P']) ? $jkInput : null;

            $username = trim((string)($row['username_opsional_kosongkan_untuk_otomatis'] ?? ''));
            if (empty($username)) {
                // Auto generate sequence: Prefix + Number (e.g. P202600 + 1 => P2026001)
                $username = $prefix . $currentNumber;
                $currentNumber++;
            }

            $password = trim((string)($row['password_opsional_kosongkan_untuk_otomatis'] ?? ''));
            if (empty($password)) {
                // Auto generate password
                $password = UserResource::generatePassword();
            }

            // Create user
            User::create([
                'name'                => $namaLengkap, // Name in login field
                'nama_lengkap'        => $namaLengkap, // Biodata field
                'username'            => $username,
                'email'               => $username . '@tryout.local',
                'password'            => Hash::make($password),
                'plain_password'      => $password,
                'role'                => 'peserta',
                'sekolah'             => $sekolah,
                'jenis_kelamin'       => $jenisKelamin,
                'is_biodata_complete' => false,
            ]);
        }
    }
}
