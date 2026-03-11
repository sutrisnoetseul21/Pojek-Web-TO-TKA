@extends('student.layouts.app')

@section('title', 'Biodata & Token')
@section('subtitle', 'Lengkapi Data Diri')

@section('content')
    <div class="biodata-container">
        <!-- Left Panel - Info -->
        <div class="info-panel">
            <div class="info-content">
                <div class="info-icon">
                    <svg style="width:48px;height:48px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h2 class="info-title">Selamat Datang, {{ Auth::user()->username }}</h2>
                <p class="info-text">Silakan lengkapi data diri Anda dan masukkan token tryout yang diberikan oleh pengawas.
                </p>

                <div class="info-steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <span>Lengkapi biodata</span>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <span>Masukkan token</span>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <span>Mulai tryout</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Form -->
        <div class="form-panel">
            <div class="form-card">
                <h2 class="form-title">Konfirmasi Data Peserta</h2>

                @if ($errors->any())
                    <div class="error-box">
                        <ul style="margin:0;padding-left:1.25rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('tryout.biodata') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Kode NIK</label>
                        <input type="text" class="form-input readonly" value="{{ Auth::user()->username }}" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Peserta <span class="required">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-input" placeholder="Ketikkan Nama Peserta"
                            value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tempat Lahir <span class="required">*</span></label>
                            <input type="text" name="tempat_lahir" class="form-input" placeholder="Kota kelahiran"
                                value="{{ old('tempat_lahir', $user->tempat_lahir) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Lahir <span class="required">*</span></label>
                            <input type="date" name="tanggal_lahir" class="form-input"
                                value="{{ old('tanggal_lahir', $user->tanggal_lahir?->format('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin <span class="required">*</span></label>
                        <select name="jenis_kelamin" class="form-input form-select" required>
                            <option value="">-- Pilih --</option>
                            <option value="L" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'L' ? 'selected' : '' }}>
                                Laki-Laki</option>
                            <option value="P" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'P' ? 'selected' : '' }}>
                                Perempuan</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Asal Sekolah <span class="required">*</span></label>
                        <input type="text" name="sekolah" class="form-input" placeholder="Nama sekolah"
                            value="{{ old('sekolah', $user->sekolah) }}" required>
                    </div>

                    <hr class="divider">

                    <div class="form-group">
                        <label class="form-label">Token <span class="required">*</span></label>
                        <input type="text" name="token" class="form-input token-input" placeholder="Ketikkan token di sini"
                            value="{{ old('token') }}" maxlength="6" required>
                        <small class="form-hint">Token 6 karakter diberikan oleh pengawas</small>
                    </div>

                    <button type="submit" class="btn-submit">
                        Submit
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            main {
                padding: 0 !important;
            }

            .biodata-container {
                display: flex;
                min-height: calc(100vh - 72px);
            }

            .info-panel {
                flex: 0 0 40%;
                background: linear-gradient(180deg, #1e3a5f 0%, #0d1f3c 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
            }

            .info-content {
                max-width: 350px;
                color: white;
                text-align: center;
            }

            .info-icon {
                margin-bottom: 1.5rem;
            }

            .info-title {
                font-size: 1.5rem;
                font-weight: 700;
                margin-bottom: 1rem;
            }

            .info-text {
                font-size: 0.875rem;
                opacity: 0.8;
                line-height: 1.6;
                margin-bottom: 2rem;
            }

            .info-steps {
                display: flex;
                flex-direction: column;
                gap: 1rem;
                text-align: left;
            }

            .step {
                display: flex;
                align-items: center;
                gap: 1rem;
                font-size: 0.875rem;
            }

            .step-number {
                width: 28px;
                height: 28px;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
            }

            .form-panel {
                flex: 1;
                background: #f3f4f6;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
            }

            .form-card {
                background: white;
                border-radius: 0.5rem;
                padding: 2rem;
                width: 100%;
                max-width: 480px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            }

            .form-title {
                font-size: 1.25rem;
                font-weight: 700;
                color: #1f2937;
                margin-bottom: 1.5rem;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .form-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }

            .form-label {
                display: block;
                font-size: 0.75rem;
                font-weight: 500;
                color: #6b7280;
                margin-bottom: 0.375rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }

            .required {
                color: #ef4444;
            }

            .form-input {
                width: 100%;
                padding: 0.75rem 1rem;
                border: 1px solid #d1d5db;
                border-radius: 0.375rem;
                font-size: 0.875rem;
                transition: border-color 0.2s, box-shadow 0.2s;
            }

            .form-input:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }

            .form-input.readonly {
                background: #f9fafb;
                color: #6b7280;
            }

            .form-select {
                appearance: none;
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
                background-position: right 0.75rem center;
                background-repeat: no-repeat;
                background-size: 1.25rem;
                padding-right: 2.5rem;
            }

            .divider {
                border: none;
                border-top: 1px solid #e5e7eb;
                margin: 1.5rem 0;
            }

            .token-input {
                text-transform: uppercase;
                letter-spacing: 0.1em;
                font-weight: 500;
            }

            .form-hint {
                display: block;
                font-size: 0.75rem;
                color: #9ca3af;
                margin-top: 0.375rem;
            }

            .btn-submit {
                width: 100%;
                padding: 0.875rem;
                background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 100%);
                color: white;
                border: none;
                border-radius: 0.375rem;
                font-size: 0.875rem;
                font-weight: 600;
                cursor: pointer;
                margin-top: 1rem;
                transition: all 0.3s;
            }

            .btn-submit:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
            }

            .error-box {
                background: #fef2f2;
                border: 1px solid #fecaca;
                color: #b91c1c;
                padding: 0.75rem 1rem;
                margin-bottom: 1.5rem;
                border-radius: 0.375rem;
                font-size: 0.875rem;
            }

            @media (max-width: 768px) {
                .biodata-container {
                    flex-direction: column;
                }

                .info-panel {
                    flex: none;
                    padding: 2rem 1rem;
                }

                .form-row {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // Auto uppercase token input
            document.querySelector('.token-input').addEventListener('input', function (e) {
                this.value = this.value.toUpperCase();
            });
        </script>
    @endpush
@endsection