<?php

namespace App\Traits;

use App\Models\JadwalRuanganProktor;
use App\Models\JadwalTryout;
use Illuminate\Database\Eloquent\Builder;

trait HasProktorFilter
{
    /**
     * Apply Proktor room-based isolation filter to the query.
     */
    protected function applyProktorFilter(Builder $query): Builder
    {
        $user = auth()->user();

        if ($user && $user->role === 'proktor') {
            // 1. Ambil Jadwal aktif hari ini
            $activeJadwalIds = JadwalTryout::where('is_active', true)
                ->where('tgl_mulai', '<=', now()->endOfDay())
                ->where('tgl_selesai', '>=', now()->startOfDay())
                ->pluck('id');

            // 2. Ambil ID Ruangan & Kelas yang ditugaskan ke proktor ini (status aktif)
            $assignedData = JadwalRuanganProktor::where('proktor_id', $user->id)
                ->where('status', 'active')
                ->whereIn('jadwal_tryout_id', $activeJadwalIds)
                ->get(['ruangan_id', 'kelas_id']);

            // Optimasi: Kelompokkan ruang-kelas
            $allClassRuanganIds = $assignedData->where('kelas_id', null)->pluck('ruangan_id')->toArray();
            $specificClassData = $assignedData->where('kelas_id', '!=', null)->groupBy('ruangan_id');

            return $query->where(function ($q) use ($allClassRuanganIds, $specificClassData) {
                if (!empty($allClassRuanganIds)) {
                    $q->orWhereIn('ruangan_id', $allClassRuanganIds);
                }

                foreach ($specificClassData as $ruanganId => $items) {
                    $kelasIds = $items->pluck('kelas_id')->toArray();
                    $q->orWhere(function ($sq) use ($ruanganId, $kelasIds) {
                        $sq->where('ruangan_id', $ruanganId)
                           ->whereHas('user', fn ($uq) => $uq->whereIn('kelas_id', $kelasIds));
                    });
                }
            });
        }

        return $query;
    }

    /**
     * Apply Proktor filter on JadwalTryout query.
     */
    protected function applyProktorJadwalFilter(Builder $query): Builder
    {
        $user = auth()->user();

        if ($user && $user->role === 'proktor') {
            $assignedJadwalIds = JadwalRuanganProktor::where('proktor_id', $user->id)
                ->where('status', 'active')
                ->pluck('jadwal_tryout_id');

            return $query->whereIn('id', $assignedJadwalIds);
        }

        return $query;
    }
}
