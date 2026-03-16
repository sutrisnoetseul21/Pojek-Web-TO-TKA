<?php

namespace App\Http\Controllers;

use App\Models\BankSoal;
use App\Models\JadwalTryout;
use App\Models\JawabanPeserta;
use App\Models\PesertaJadwal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    /**
     * Tampilkan halaman login
     */
    public function showLogin()
    {
        return view('student.login');
    }

    /**
     * Proses login peserta
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'username' => 'Username atau password salah.',
            ])->withInput();
        }

        if ($user->role !== 'peserta') {
            return back()->withErrors([
                'username' => 'Akun ini bukan akun peserta.',
            ])->withInput();
        }

        Auth::login($user);

        // Jika biodata belum lengkap, redirect ke form biodata
        if (!$user->is_biodata_complete) {
            return redirect()->route('tryout.biodata');
        }

        // Jika sudah lengkap, redirect ke halaman utama dengan pilihan input token
        return redirect()->route('tryout.biodata');
    }

    /**
     * Tampilkan form biodata dan input token
     */
    public function showBiodata()
    {
        $user = Auth::user();

        // Cek apakah ada tryout yang sedang berlangsung
        $activeSession = PesertaJadwal::where('user_id', $user->id)
            ->where('status', 'started')
            ->first();

        if ($activeSession) {
            return redirect()->route('tryout.soal', $activeSession);
        }

        return view('student.biodata', [
            'user' => $user,
        ]);
    }

    /**
     * Simpan biodata dan validasi token
     */
    public function storeBiodata(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'token' => 'required|string|size:6',
        ]);

        // Validasi token
        $jadwal = JadwalTryout::where('token', strtoupper($request->token))
            ->where('is_active', true)
            ->first();

        if (!$jadwal) {
            return back()->withErrors([
                'token' => 'Token tidak valid atau jadwal tidak aktif.',
            ])->withInput();
        }

        // Cek apakah jadwal sudah dimulai dan belum selesai
        $now = now();
        if ($now < $jadwal->tgl_mulai) {
            return back()->withErrors([
                'token' => 'Jadwal tryout belum dimulai. Mulai: ' . $jadwal->tgl_mulai->format('d M Y, H:i'),
            ])->withInput();
        }

        if ($now > $jadwal->tgl_selesai) {
            return back()->withErrors([
                'token' => 'Jadwal tryout sudah berakhir.',
            ])->withInput();
        }

        // Cek kuota
        if ($jadwal->kuota_peserta) {
            $registered = PesertaJadwal::where('jadwal_tryout_id', $jadwal->id)->count();
            if ($registered >= $jadwal->kuota_peserta) {
                return back()->withErrors([
                    'token' => 'Kuota peserta untuk jadwal ini sudah penuh.',
                ])->withInput();
            }
        }

        // Cek apakah user sudah terdaftar di jadwal ini
        $existingRegistration = PesertaJadwal::where('user_id', $user->id)
            ->where('jadwal_tryout_id', $jadwal->id)
            ->first();

        if ($existingRegistration) {
            // Jika sudah terdaftar, langsung redirect ke konfirmasi
            return redirect()->route('tryout.konfirmasi', $jadwal);
        }

        // Update biodata user
        $user->update([
            'nama_lengkap' => $request->nama_lengkap,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'sekolah' => $user->sekolahRelation->nama_sekolah ?? $user->sekolah,
            'jenis_kelamin' => $request->jenis_kelamin,
            'is_biodata_complete' => true,
        ]);

        // Daftarkan peserta ke jadwal
        PesertaJadwal::create([
            'user_id' => $user->id,
            'jadwal_tryout_id' => $jadwal->id,
            'token_used' => strtoupper($request->token),
            'status' => 'registered',
        ]);

        return redirect()->route('tryout.konfirmasi', $jadwal);
    }

    /**
     * Tampilkan konfirmasi sebelum mulai tryout
     */
    public function konfirmasi(JadwalTryout $jadwal)
    {
        $user = Auth::user();

        $pesertaJadwal = PesertaJadwal::where('user_id', $user->id)
            ->where('jadwal_tryout_id', $jadwal->id)
            ->firstOrFail();

        // Jika sudah started, redirect ke soal
        if ($pesertaJadwal->status === 'started') {
            return redirect()->route('tryout.soal', $pesertaJadwal);
        }

        // Jika sudah completed, redirect ke hasil
        if ($pesertaJadwal->status === 'completed') {
            return redirect()->route('tryout.hasil', $pesertaJadwal);
        }

        // Hitung total waktu dari paket
        $paket = $jadwal->paketTryout;
        $totalWaktu = $paket->mapelItems->sum('waktu_mapel');
        $totalSoal = $paket->mapelItems->sum(function ($item) {
            if ($item->mode === 'MANUAL' && !empty($item->soal_ids)) {
                return count($item->soal_ids);
            }
            return $item->jumlah_soal;
        });

        // Ambil daftar mapel + info
        $mapelList = $paket->mapelItems()->with('mapel')->orderBy('urutan')->get();

        return view('student.konfirmasi', [
            'jadwal' => $jadwal,
            'paket' => $paket,
            'pesertaJadwal' => $pesertaJadwal,
            'totalWaktu' => $totalWaktu,
            'totalSoal' => $totalSoal,
            'mapelList' => $mapelList,
        ]);
    }

    /**
     * Mulai tryout
     */
    public function mulai(JadwalTryout $jadwal)
    {
        $user = Auth::user();

        $pesertaJadwal = PesertaJadwal::where('user_id', $user->id)
            ->where('jadwal_tryout_id', $jadwal->id)
            ->firstOrFail();

        if ($pesertaJadwal->status !== 'registered') {
            return redirect()->route('tryout.soal', $pesertaJadwal);
        }

        // Hitung total waktu
        $totalWaktu = $jadwal->paketTryout->mapelItems->sum('waktu_mapel');

        $firstMapel = $jadwal->paketTryout->mapelItems()->orderBy('urutan')->first();

        // Update status dan waktu mulai
        $pesertaJadwal->update([
            'status' => 'started',
            'waktu_mulai' => now(),
            'sisa_waktu' => $totalWaktu, // dalam menit
            'current_mapel_id' => $firstMapel?->mapel_id,
        ]);

        return redirect()->route('tryout.soal', $pesertaJadwal);
    }

    /**
     * Tampilkan halaman soal
     */
    public function soal(PesertaJadwal $pesertaJadwal)
    {
        // Cek ownership
        if ($pesertaJadwal->user_id !== Auth::id()) {
            abort(403);
        }

        if ($pesertaJadwal->status === 'completed') {
            return redirect()->route('tryout.hasil', $pesertaJadwal);
        }

        $jadwal = $pesertaJadwal->jadwalTryout;
        $paket = $jadwal->paketTryout;

        // Ambil soal dikelompokkan per mapel
        $mapelSections = [];
        foreach ($paket->mapelItems()->with('mapel')->orderBy('urutan')->get() as $mapelItem) {
            $soal = $mapelItem->getSoal();
            $mapelSections[] = [
                'mapel_id' => $mapelItem->mapel_id,
                'nama_mapel' => $mapelItem->mapel->nama_mapel ?? 'Mapel',
                'waktu_menit' => $mapelItem->waktu_mapel,
                'soal' => $soal->values()->toArray(),
            ];
        }

        // Ambil jawaban yang sudah ada
        $jawabanMap = JawabanPeserta::where('peserta_jadwal_id', $pesertaJadwal->id)
            ->pluck('jawaban', 'bank_soal_id')
            ->toArray();

        $raguMap = JawabanPeserta::where('peserta_jadwal_id', $pesertaJadwal->id)
            ->pluck('is_ragu', 'bank_soal_id')
            ->toArray();

        return view('student.soal', [
            'pesertaJadwal' => $pesertaJadwal,
            'jadwal' => $jadwal,
            'paket' => $paket,
            'mapelSections' => $mapelSections,
            'jawabanMap' => $jawabanMap,
            'raguMap' => $raguMap,
        ]);
    }

    /**
     * Simpan jawaban (via AJAX)
     */
    public function simpanJawaban(Request $request)
    {
        $request->validate([
            'peserta_jadwal_id' => 'required|exists:peserta_jadwal,id',
            'bank_soal_id' => 'required|exists:bank_soal,id',
            'jawaban' => 'required',
        ]);

        $pesertaJadwal = PesertaJadwal::findOrFail($request->peserta_jadwal_id);

        // Guard: Jika sudah completed (di-force Admin)
        if ($pesertaJadwal->status === 'completed') {
            return response()->json([
                'status' => 'force_reload',
                'message' => 'Sesi ujian telah ditutup oleh Pengawas.'
            ], 403);
        }

        // Guard: JIKA Admin memaksa lanjut mapel, dan mapel_id dikirim dari frontend
        if ($request->has('mapel_id') && $pesertaJadwal->current_mapel_id != $request->mapel_id) {
            return response()->json([
                'status' => 'force_reload',
                'message' => 'Sesi mapel telah berganti. Mengalihkan...'
            ], 403);
        }

        // Cek ownership
        if ($pesertaJadwal->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        JawabanPeserta::updateOrCreate(
            [
                'peserta_jadwal_id' => $request->peserta_jadwal_id,
                'bank_soal_id' => $request->bank_soal_id,
            ],
            [
                'jawaban' => $request->jawaban,
            ]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Toggle ragu-ragu
     */
    public function toggleRagu(JawabanPeserta $jawaban)
    {
        // Cek ownership
        if ($jawaban->pesertaJadwal->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $jawaban->update([
            'is_ragu' => !$jawaban->is_ragu,
        ]);

        return response()->json(['success' => true, 'is_ragu' => $jawaban->is_ragu]);
    }

    /**
     * Tampilkan halaman konfirmasi selesai
     */
    public function showSelesai(PesertaJadwal $pesertaJadwal)
    {
        // Cek ownership
        if ($pesertaJadwal->user_id !== Auth::id()) {
            abort(403);
        }

        $totalSoal = $pesertaJadwal->jadwalTryout->paketTryout->mapelItems->sum(function ($item) {
            if ($item->mode === 'MANUAL' && !empty($item->soal_ids)) {
                return count($item->soal_ids);
            }
            return $item->jumlah_soal;
        });
        $dijawab = JawabanPeserta::where('peserta_jadwal_id', $pesertaJadwal->id)->count();
        $ragu = JawabanPeserta::where('peserta_jadwal_id', $pesertaJadwal->id)
            ->where('is_ragu', true)->count();

        return view('student.selesai', [
            'pesertaJadwal' => $pesertaJadwal,
            'totalSoal' => $totalSoal,
            'dijawab' => $dijawab,
            'ragu' => $ragu,
        ]);
    }

    /**
     * Submit tryout dan hitung nilai
     */
    public function submit(PesertaJadwal $pesertaJadwal)
    {
        // Cek ownership
        if ($pesertaJadwal->user_id !== Auth::id()) {
            abort(403);
        }

        $pesertaJadwal->calculateAndSubmit(true);

        return redirect()->route('tryout.hasil', $pesertaJadwal);
    }

    /**
     * Tampilkan hasil tryout
     */
    public function hasil(PesertaJadwal $pesertaJadwal)
    {
        // Cek ownership
        if ($pesertaJadwal->user_id !== Auth::id()) {
            abort(403);
        }

        if ($pesertaJadwal->status !== 'completed') {
            return redirect()->route('tryout.soal', $pesertaJadwal);
        }

        $jawaban = JawabanPeserta::where('peserta_jadwal_id', $pesertaJadwal->id)
            ->with('bankSoal.jawaban')
            ->get();

        return view('student.hasil', [
            'pesertaJadwal' => $pesertaJadwal,
            'jawaban' => $jawaban,
        ]);
    }

    /**
     * Logout peserta
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('tryout.login');
    }
}
