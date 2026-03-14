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
        
        $query = User::where('role', 'peserta')
            ->with(['sekolahRelation', 'kelas'])
            ->orderBy('username');

        if ($ids) {
            $idArray = explode(',', $ids);
            $query->whereIn('id', $idArray);
            $filterLabel = count($idArray) . ' Peserta Terpilih';
        } else {
            if ($sekolahId) {
                $query->where('sekolah_id', $sekolahId);
                $sekolah = \App\Models\Sekolah::find($sekolahId);
                $filterLabel = $sekolah ? $sekolah->nama_sekolah : 'Sekolah Terpilih';
                
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
        ]);
    }
}
