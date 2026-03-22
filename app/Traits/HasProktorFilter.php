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

            // 2. Ambil ID Ruangan yang ditugaskan ke proktor ini (status aktif)
            $assignedRuanganIds = JadwalRuanganProktor::where('proktor_id', $user->id)
                ->where('status', 'active')
                ->whereIn('jadwal_tryout_id', $activeJadwalIds)
                ->pluck('ruangan_id');

            return $query->whereIn('ruangan_id', $assignedRuanganIds);
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
