<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class KartuPesertaController extends Controller
{
    public function print(Request $request)
    {
        $ids = $request->query('ids');
        $sekolah = $request->query('sekolah');
        
        $query = User::where('role', 'peserta')
            ->orderBy('sekolah')
            ->orderBy('username');

        if ($ids) {
            $idArray = explode(',', $ids);
            $query->whereIn('id', $idArray);
        } elseif ($sekolah) {
            if ($sekolah !== 'semua') {
                $query->where('sekolah', $sekolah);
            }
        } else {
            abort(400, 'Tidak ada filter yang dipilih untuk dicetak.');
        }

        $users = $query->get();
        
        if ($users->isEmpty()) {
            abort(404, 'Tidak ada data peserta yang ditemukan.');
        }

        $filterLabel = $sekolah && $sekolah !== 'semua' ? $sekolah : ($ids ? count(explode(',', $ids)) . ' peserta dipilih' : 'Semua Peserta');
        
        return view('print.kartu-peserta', [
            'users' => $users,
            'filterLabel' => $filterLabel,
        ]);
    }
}
