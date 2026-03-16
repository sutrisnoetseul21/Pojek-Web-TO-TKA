<?php

namespace App\Filament\Widgets;

use App\Models\BankSoal;
use App\Models\JadwalTryout;
use App\Models\PesertaJadwal;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Carbon;

class DashboardStatsOverview extends BaseWidget
{
    /**
     * Set urutan widget menjadi pertama
     */
    protected static ?int $sort = 1;

    /**
     * Waktu polling auto-refresh (10 detik)
     */
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $today = Carbon::today();

        // 1. Peserta Sedang Ujian
        $pesertaUjianCount = PesertaJadwal::where('status', 'started')->count();

        // 2. Total Peserta
        $totalPesertaCount = User::where('role', 'peserta')->count();

        // 3. Tryout Aktif Hari Ini
        $tryoutAktifCount = JadwalTryout::where('is_active', true)
            ->whereDate('tgl_mulai', '<=', $today)
            ->whereDate('tgl_selesai', '>=', $today)
            ->count();

        // 4. Total Bank Soal
        $totalSoalCount = BankSoal::count();

        return [
            Stat::make('Peserta Sedang Ujian', $pesertaUjianCount)
                ->description('Peserta aktif saat ini')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Opsional: Sparkline fiktif agar menarik

            Stat::make('Total Peserta', $totalPesertaCount)
                ->description('Seluruh siswa terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Tryout Aktif', $tryoutAktifCount)
                ->description('Jadwal hari ini')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make('Total Bank Soal', $totalSoalCount)
                ->description('Pertanyaan tersimpan')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('gray'),
        ];
    }
}
