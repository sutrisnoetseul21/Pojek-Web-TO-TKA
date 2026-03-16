<?php

namespace App\Filament\Widgets;

use App\Models\PesertaJadwal;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PartisipasiChart extends ChartWidget
{
    protected static ?string $heading = 'Partisipasi Tryout (7 Hari Terakhir)';
    
    /**
     * Urutan Widget (Ke-3, di bawah)
     */
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        // 1. Dapatkan periode 7 hari terakhir (Hari ke-1 sampai ke-7)
        $startDate = Carbon::today()->subDays(6); // Total 7 hari dengan hari ini
        $endDate = Carbon::today();

        // 2. Query Aggregate DB agar native tanpa package external
        $trendData = PesertaJadwal::select(
                DB::raw('DATE(waktu_selesai) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->where('status', 'completed')
            ->whereDate('waktu_selesai', '>=', $startDate)
            ->whereDate('waktu_selesai', '<=', $endDate)
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        // 3. Format Array Sumbu X (Label) dan Sumbu Y (Data)
        $labels = [];
        $data = [];

        // Loop untuk memastikan setiap hari muncul meskipun count-nya 0
        for ($i = 0; $i < 7; $i++) {
            $datePoint = $startDate->copy()->addDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($datePoint)->translatedFormat('d M'); // Format misal: 10 Okt
            
            // Masukkan data jika ada, jika tidak ada fallback ke 0
            $data[] = $trendData[$datePoint] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Peserta Selesai',
                    'data' => $data,
                    'borderColor' => '#3b82f6', // Bootstrap Blue (Primary)
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4, // Membuat line sedikit melengkung (smooth)
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
