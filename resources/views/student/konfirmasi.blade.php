@extends('student.layouts.app')

@section('title', 'Konfirmasi Tryout')
@section('subtitle', 'Konfirmasi Tes')

@section('content')
    <div class="konfirmasi-container">
        <div class="konfirmasi-card">
            <h2 class="konfirmasi-title">Konfirmasi Tes</h2>

            <div class="info-row">
                <span class="info-label">Nama Tes</span>
                <span class="info-value">{{ $paket->nama_paket }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Status Tes</span>
                <span class="info-value">
                    @if($pesertaJadwal->status === 'started')
                        Sedang Berlangsung
                    @elseif($pesertaJadwal->status === 'completed')
                        Selesai
                    @else
                        Tes Baru
                    @endif
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Waktu Tes</span>
                <span class="info-value">{{ $jadwal->tgl_mulai->format('d/m/Y H:i') }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Alokasi Waktu Tes</span>
                <span class="info-value">{{ $totalWaktu }} Menit</span>
            </div>

            <div class="info-row">
                <span class="info-label">Total Soal</span>
                <span class="info-value">{{ $totalSoal }} Soal</span>
            </div>

            {{-- Daftar Mapel --}}
            <div class="mapel-section">
                <span class="info-label" style="margin-bottom: 0.75rem; display: block;">Mata Pelajaran</span>
                @foreach ($mapelList as $i => $item)
                    <div class="mapel-item">
                        <div class="mapel-number">{{ $i + 1 }}</div>
                        <div class="mapel-info">
                            <div class="mapel-name">{{ $item->mapel->nama_mapel ?? '-' }}</div>
                            <div class="mapel-detail">
                                @if($item->mode === 'MANUAL' && !empty($item->soal_ids))
                                    {{ count($item->soal_ids) }} soal
                                @else
                                    {{ $item->jumlah_soal }} soal
                                @endif
                                • {{ $item->waktu_mapel }} menit
                                • {{ $item->mode === 'MANUAL' ? 'Pilih Manual' : 'Acak' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <form action="{{ route('tryout.mulai', $jadwal) }}" method="POST">
                @csrf
                <button type="submit" class="btn-mulai">
                    🚀 Mulai Tryout
                </button>
            </form>
        </div>
    </div>

    @push('styles')
        <style>
            .konfirmasi-container {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: calc(100vh - 200px);
                padding: 2rem 1rem;
            }

            .konfirmasi-card {
                background: white;
                border-radius: 0.75rem;
                padding: 2rem 2.5rem;
                width: 100%;
                max-width: 500px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            }

            .konfirmasi-title {
                font-size: 1.5rem;
                font-weight: 600;
                color: #1f2937;
                margin-bottom: 1.5rem;
            }

            .info-row {
                padding: 0.75rem 0;
                border-bottom: 1px solid #f3f4f6;
            }

            .info-row:last-of-type {
                border-bottom: none;
            }

            .info-label {
                display: block;
                font-size: 0.75rem;
                color: #9ca3af;
                margin-bottom: 0.25rem;
                text-transform: uppercase;
                letter-spacing: 0.03em;
            }

            .info-value {
                display: block;
                font-size: 0.9375rem;
                color: #1f2937;
                font-weight: 500;
            }

            .mapel-section {
                padding: 1rem 0;
                margin-bottom: 0.5rem;
            }

            .mapel-item {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.625rem 0.75rem;
                background: #f8fafc;
                border-radius: 0.5rem;
                margin-bottom: 0.5rem;
                border: 1px solid #e2e8f0;
            }

            .mapel-number {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                background: linear-gradient(135deg, #3b82f6, #60a5fa);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 0.8125rem;
                flex-shrink: 0;
            }

            .mapel-info {
                flex: 1;
            }

            .mapel-name {
                font-weight: 600;
                font-size: 0.875rem;
                color: #1e293b;
            }

            .mapel-detail {
                font-size: 0.75rem;
                color: #64748b;
                margin-top: 0.125rem;
            }

            .btn-mulai {
                width: 100%;
                padding: 0.875rem;
                background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 100%);
                color: white;
                border: none;
                border-radius: 0.5rem;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                margin-top: 1rem;
            }

            .btn-mulai:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
            }
        </style>
    @endpush
@endsection