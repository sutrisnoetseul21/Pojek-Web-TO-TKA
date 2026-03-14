@extends('student.layouts.app')

@section('title', 'Hasil Tryout')
@section('subtitle', 'Review Hasil')

@section('content')
    <div class="hasil-container">
        <div class="hasil-card">
            <!-- Header -->
            <div class="hasil-header">
                <div class="hasil-icon">
                    <svg style="width:36px;height:36px;color:#10b981;" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h2 class="hasil-title">Reviu Hasil Simulasi</h2>
                <p class="hasil-paket">{{ $pesertaJadwal->jadwalTryout->paketTryout->nama_paket }}</p>
            </div>

            <!-- Skor Total -->
            <div class="skor-box">
                <p class="skor-label">Total Nilai Anda</p>
                <p class="skor-value">{{ $pesertaJadwal->total_nilai ?? 0 }}</p>
            </div>

            <!-- Tabel Jawaban -->
            <div class="tabel-wrapper">
                <table class="tabel-jawaban">
                    <thead>
                        <tr>
                            <th>NO.</th>
                            <th>JAWABAN ANDA</th>
                            <th>SKOR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jawaban as $index => $j)
                            @php
                                $soal = $j->bankSoal;
                                $jawabanUser = $j->jawaban;

                                // Handle array jawaban
                                if (is_array($jawabanUser)) {
                                    if ($soal->tipe_soal === 'BENAR_SALAH') {
                                        // Format: [opsi_id => 'BENAR'/'SALAH']
                                        $labels = [];
                                        if ($soal) {
                                            $jawabanSoal = $soal->jawaban ?? collect();
                                            foreach ($jawabanSoal as $opsi) {
                                                $ans = $jawabanUser[$opsi->id] ?? '-';
                                                // Create label like: "Pernyataan A: BENAR"
                                                // Or just concise: "BENAR, SALAH, ..." in order
                                                $labels[] = $ans;
                                            }
                                        }
                                        $jawabanText = implode(', ', $labels);
                                    } else {
                                        // PG_KOMPLEKS (Array of IDs)
                                        $labels = [];
                                        if ($soal) {
                                            $jawabanSoal = $soal->jawaban ?? collect();
                                            foreach ($jawabanUser as $uid) {
                                                $found = $jawabanSoal->firstWhere('id', (int) $uid);
                                                $labels[] = $found ? $found->teks_jawaban : $uid;
                                            }
                                        }
                                        $jawabanText = implode(', ', $labels);
                                    }
                                } else {
                                    // Single answer - lookup the text
                                    if ($soal && $jawabanUser) {
                                        $jawabanSoal = $soal->jawaban ?? collect();
                                        $found = $jawabanSoal->firstWhere('id', (int) $jawabanUser);
                                        $jawabanText = $found ? $found->teks_jawaban : $jawabanUser;
                                    } else {
                                        $jawabanText = null;
                                    }
                                }

                                // Calculate skor for this answer
                                $skor = 0;
                                if ($soal) {
                                    $jawabanSoal = $soal->jawaban ?? collect();
                                    $userJawaban = $j->jawaban;

                                    if ($soal->tipe_soal === 'BENAR_SALAH') {
                                        if (is_array($userJawaban)) {
                                            foreach ($jawabanSoal as $opsi) {
                                                $ansUser = strtoupper($userJawaban[$opsi->id] ?? '');
                                                $kunci = strtoupper($opsi->kunci_jawaban ?? '');

                                                if ($kunci) {
                                                    if ($ansUser === $kunci) {
                                                        $skor += $opsi->skor ?? 0;
                                                    }
                                                } else {
                                                    // Fallback logic
                                                    if ($opsi->skor > 0 && $ansUser === 'BENAR') {
                                                        $skor += $opsi->skor;
                                                    }
                                                }
                                            }
                                        }
                                    } elseif ($soal->tipe_soal === 'PG_KOMPLEKS') {
                                        if (is_array($userJawaban)) {
                                            foreach ($userJawaban as $uid) {
                                                $found = $jawabanSoal->firstWhere('id', (int) $uid);
                                                if ($found) {
                                                    $skor += $found->skor ?? 0;
                                                }
                                            }
                                        }
                                    } else {
                                        // PG_TUNGGAL or others
                                        $found = $jawabanSoal->firstWhere('id', (int) $userJawaban);
                                        if ($found) {
                                            $skor = $found->skor ?? 0;
                                        }
                                    }
                                }
                            @endphp
                            <tr class="{{ $loop->even ? 'even' : '' }}">
                                <td class="col-no">{{ $index + 1 }}</td>
                                <td class="col-jawaban">
                                    @if($jawabanText)
                                        <span class="jawaban-text {{ $skor > 0 ? 'benar' : 'salah' }}">
                                            {{ $jawabanText }}
                                        </span>
                                        @if($skor > 0)
                                            <span class="badge-benar">✓</span>
                                        @else
                                            <span class="badge-salah">✗</span>
                                        @endif
                                    @else
                                        <span class="jawaban-kosong">(Tidak dijawab)</span>
                                    @endif
                                </td>
                                <td class="col-skor">
                                    <span class="skor-badge {{ $skor > 0 ? 'skor-plus' : '' }}">{{ $skor }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Tombol Keluar -->
            <div class="hasil-actions">
                <form action="{{ route('tryout.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-keluar">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .hasil-container {
                display: flex;
                justify-content: center;
                padding: 2rem 1rem;
                min-height: calc(100vh - 200px);
            }

            .hasil-card {
                background: white;
                border-radius: 0.75rem;
                padding: 2rem;
                width: 100%;
                max-width: 640px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            }

            .hasil-header {
                text-align: center;
                margin-bottom: 1.5rem;
            }

            .hasil-icon {
                width: 64px;
                height: 64px;
                background: #ecfdf5;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 0.75rem;
            }

            .hasil-title {
                font-size: 1.375rem;
                font-weight: 700;
                color: #1f2937;
                margin-bottom: 0.25rem;
            }

            .hasil-paket {
                color: #6b7280;
                font-size: 0.9rem;
            }

            .skor-box {
                background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
                border-radius: 0.75rem;
                padding: 1.5rem;
                text-align: center;
                color: white;
                margin-bottom: 1.5rem;
            }

            .skor-label {
                font-size: 0.8rem;
                opacity: 0.8;
                margin-bottom: 0.25rem;
            }

            .skor-value {
                font-size: 3rem;
                font-weight: 800;
            }

            .tabel-wrapper {
                border: 1px solid #e5e7eb;
                border-radius: 0.5rem;
                overflow: hidden;
                margin-bottom: 1.5rem;
            }

            .tabel-jawaban {
                width: 100%;
                border-collapse: collapse;
            }

            .tabel-jawaban thead tr {
                background: #1e3a5f;
                color: white;
            }

            .tabel-jawaban th {
                padding: 0.625rem 1rem;
                text-align: left;
                font-size: 0.75rem;
                font-weight: 600;
                letter-spacing: 0.03em;
            }

            .tabel-jawaban td {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
                border-bottom: 1px solid #f3f4f6;
            }

            .tabel-jawaban tr.even {
                background: #f9fafb;
            }

            .col-no {
                font-weight: 600;
                color: #3b82f6;
                width: 50px;
            }

            .col-skor {
                width: 60px;
                text-align: center;
            }

            .jawaban-text {
                color: #374151;
            }

            .jawaban-text.benar {
                color: #059669;
            }

            .jawaban-text.salah {
                color: #dc2626;
            }

            .jawaban-kosong {
                color: #9ca3af;
                font-style: italic;
                font-size: 0.8rem;
            }

            .badge-benar {
                display: inline-block;
                background: #ecfdf5;
                color: #059669;
                font-size: 0.7rem;
                font-weight: 700;
                padding: 0.1rem 0.4rem;
                border-radius: 0.25rem;
                margin-left: 0.375rem;
            }

            .badge-salah {
                display: inline-block;
                background: #fef2f2;
                color: #dc2626;
                font-size: 0.7rem;
                font-weight: 700;
                padding: 0.1rem 0.4rem;
                border-radius: 0.25rem;
                margin-left: 0.375rem;
            }

            .skor-badge {
                display: inline-block;
                padding: 0.15rem 0.5rem;
                border-radius: 0.25rem;
                font-weight: 600;
                font-size: 0.8rem;
                background: #f3f4f6;
                color: #6b7280;
            }

            .skor-badge.skor-plus {
                background: #ecfdf5;
                color: #059669;
            }

            .hasil-actions {
                text-align: center;
            }

            .btn-keluar {
                padding: 0.75rem 2.5rem;
                background: #1e40af;
                color: white;
                border: none;
                border-radius: 0.5rem;
                font-weight: 600;
                font-size: 0.9rem;
                cursor: pointer;
                transition: all 0.2s;
            }

            .btn-keluar:hover {
                background: #1e3a8a;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
            }
        </style>
    @endpush
@endsection