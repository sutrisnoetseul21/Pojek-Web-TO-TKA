<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    /**
     * Meng-override judul halaman dashboard secara dinamis.
     */
    public function getTitle(): string | Htmlable
    {
        $user = auth()->user();

        // Jika dia admin biasa dan terikat ke sekolah, tampilkan nama sekolahnya
        if ($user?->role === 'admin') {
            return $user->sekolahRelation?->nama_sekolah ?? 'Dashboard Ujian';
        }

        return 'Dashboard';
    }
}
