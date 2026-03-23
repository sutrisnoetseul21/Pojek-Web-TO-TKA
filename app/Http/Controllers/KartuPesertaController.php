<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class KartuPesertaController extends Controller
{
    public function print(Request $request)
    {
        $ids = $request->query('ids');
        $sekolahId = $request->query('sekolah_id');
        $kelasId = $request->query('kelas_id');
        $namaSekolah = '—';
        
        $query = User::where('role', 'peserta')
            ->with([
                'sekolahRelation',
                'kelas',
                'jadwalTryouts' => function ($q) {
                    $q->latest('jadwal_tryout.created_at')->limit(1);
                },
            ])
            ->orderBy('username');

        if ($ids) {
            $idArray = explode(',', $ids);
            $query->whereIn('id', $idArray);
            $filterLabel = count($idArray) . ' Peserta Terpilih';
            // Ambil nama sekolah dari peserta pertama
            $firstUser = User::find($idArray[0]);
            if ($firstUser && $firstUser->sekolah_id) {
                $sekolah = \App\Models\Sekolah::find($firstUser->sekolah_id);
                $namaSekolah = $sekolah ? $sekolah->nama_sekolah : '—';
            }
        } else {
            if ($sekolahId) {
                $query->where('sekolah_id', $sekolahId);
                $sekolah = \App\Models\Sekolah::find($sekolahId);
                $filterLabel = $sekolah ? $sekolah->nama_sekolah : 'Sekolah Terpilih';
                $namaSekolah = $sekolah ? $sekolah->nama_sekolah : '—';
                
                if ($kelasId) {
                    $query->where('kelas_id', $kelasId);
                    $kelas = \App\Models\Kelas::find($kelasId);
                    if ($kelas) {
                        $filterLabel .= ' - ' . $kelas->nama_kelas;
                    }
                }
            } else {
                abort(400, 'Tidak ada filter yang dipilih untuk dicetak.');
            }
        }

        $users = $query->get();
        
        if ($users->isEmpty()) {
            abort(404, 'Tidak ada data peserta yang ditemukan.');
        }

        return view('print.kartu-peserta', [
            'users' => $users,
            'filterLabel' => $filterLabel,
            'namaSekolah' => $namaSekolah,
        ]);
    }
}
