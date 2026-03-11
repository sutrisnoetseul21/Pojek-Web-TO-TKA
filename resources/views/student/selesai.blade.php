@extends('student.layouts.app')

@section('title', 'Selesai Tryout')
@section('subtitle', 'Konfirmasi Pengumpulan')

@section('content')
    <div class="selesai-container">
        <div class="selesai-card">
            <div class="selesai-icon">
                <svg style="width:40px;height:40px;color:#3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h2 class="selesai-title">Konfirmasi Selesai</h2>
            <p class="selesai-subtitle">Apakah Anda yakin ingin mengakhiri tryout?</p>

            <!-- Ringkasan -->
            <div class="ringkasan-box">
                <h3 class="ringkasan-title">Ringkasan Jawaban</h3>
                <div class="ringkasan-grid">
                    <div class="ringkasan-item">
                        <div class="ringkasan-number" style="color:#3b82f6;">{{ $dijawab }}</div>
                        <div class="ringkasan-label">Dijawab</div>
                    </div>
                    <div class="ringkasan-item">
                        <div class="ringkasan-number" style="color:#f59e0b;">{{ $ragu }}</div>
                        <div class="ringkasan-label">Ragu-ragu</div>
                    </div>
                    <div class="ringkasan-item">
                        <div class="ringkasan-number" style="color:#9ca3af;">{{ $totalSoal - $dijawab }}</div>
                        <div class="ringkasan-label">Kosong</div>
                    </div>
                </div>
            </div>

            <!-- Peringatan -->
            @if($totalSoal - $dijawab > 0)
                <div class="peringatan-box">
                    <strong>⚠️ Perhatian!</strong> Masih ada {{ $totalSoal - $dijawab }} soal yang belum dijawab.
                </div>
            @else
                <div class="sukses-box">
                    ✅ Semua soal sudah dijawab.
                </div>
            @endif

            <!-- Tombol -->
            <div class="selesai-actions">
                <a href="{{ route('tryout.soal', $pesertaJadwal) }}" class="btn-kembali">
                    ← Kembali
                </a>
                <form action="{{ route('tryout.submit', $pesertaJadwal) }}" method="POST" style="flex:1;">
                    @csrf
                    <button type="submit" class="btn-submit">
                        ✅ Selesai & Kumpulkan
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .selesai-container {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: calc(100vh - 200px);
                padding: 2rem 1rem;
            }

            .selesai-card {
                background: white;
                border-radius: 0.75rem;
                padding: 2rem 2.5rem;
                width: 100%;
                max-width: 480px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
                text-align: center;
            }

            .selesai-icon {
                width: 72px;
                height: 72px;
                background: #eff6ff;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1.25rem;
            }

            .selesai-title {
                font-size: 1.5rem;
                font-weight: 700;
                color: #1f2937;
                margin-bottom: 0.375rem;
            }

            .selesai-subtitle {
                color: #6b7280;
                font-size: 0.9rem;
                margin-bottom: 1.5rem;
            }

            .ringkasan-box {
                background: #f9fafb;
                border-radius: 0.625rem;
                padding: 1.25rem;
                margin-bottom: 1.25rem;
            }

            .ringkasan-title {
                font-weight: 600;
                color: #374151;
                margin-bottom: 1rem;
                font-size: 0.9rem;
            }

            .ringkasan-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 0.75rem;
            }

            .ringkasan-item {
                background: white;
                border-radius: 0.5rem;
                padding: 0.875rem 0.5rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            }

            .ringkasan-number {
                font-size: 1.75rem;
                font-weight: 700;
                line-height: 1;
                margin-bottom: 0.25rem;
            }

            .ringkasan-label {
                font-size: 0.75rem;
                color: #6b7280;
            }

            .peringatan-box {
                background: #fef3c7;
                border-left: 4px solid #f59e0b;
                padding: 0.75rem 1rem;
                border-radius: 0 0.5rem 0.5rem 0;
                margin-bottom: 1.5rem;
                text-align: left;
                font-size: 0.85rem;
                color: #92400e;
            }

            .sukses-box {
                background: #ecfdf5;
                border-left: 4px solid #10b981;
                padding: 0.75rem 1rem;
                border-radius: 0 0.5rem 0.5rem 0;
                margin-bottom: 1.5rem;
                text-align: left;
                font-size: 0.85rem;
                color: #065f46;
            }

            .selesai-actions {
                display: flex;
                gap: 0.75rem;
            }

            .btn-kembali {
                flex: 1;
                padding: 0.75rem;
                border: 2px solid #e5e7eb;
                border-radius: 0.5rem;
                color: #374151;
                font-weight: 600;
                font-size: 0.9rem;
                text-decoration: none;
                text-align: center;
                transition: all 0.2s;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .btn-kembali:hover {
                background: #f3f4f6;
                border-color: #d1d5db;
            }

            .btn-submit {
                width: 100%;
                padding: 0.75rem;
                background: #ef4444;
                color: white;
                border: none;
                border-radius: 0.5rem;
                font-weight: 600;
                font-size: 0.9rem;
                cursor: pointer;
                transition: all 0.2s;
            }

            .btn-submit:hover {
                background: #dc2626;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
            }
        </style>
    @endpush
@endsection