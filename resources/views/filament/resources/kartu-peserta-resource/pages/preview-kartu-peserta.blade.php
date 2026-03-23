<x-filament-panels::page>
    <style>
        @media screen {
            .preview-container {
                background: #f3f4f6;
                padding: 40px 20px;
                display: flex;
                justify-content: center;
                min-height: 100vh;
            }
            .a4-mockup {
                background: white;
                width: 210mm;
                min-height: 297mm;
                padding: 20mm;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                border-radius: 4px;
            }
        }
        
        @media print {
            .preview-container {
                padding: 0;
                background: white;
            }
            .a4-mockup {
                width: 100%;
                box-shadow: none;
                padding: 0;
                border-radius: 0;
            }
            .fi-header, .fi-sidebar, .fi-topbar, .fi-actions {
                display: none !important;
            }
        }

        .kartu-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .kartu {
            background: white;
            border: 1px solid #000;
            break-inside: avoid;
            page-break-inside: avoid;
            font-size: 11px;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* ── Header Kartu ── */
        .kartu-header {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #000;
        }

        .kartu-header td {
            vertical-align: middle;
            padding: 8px 10px;
        }

        .col-logo {
            width: 45px;
            text-align: center;
        }

        .col-logo .logo-placeholder {
            width: 30px;
            height: 30px;
            border: 1px dashed #718096;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7px;
            color: #718096;
            border-radius: 50%;
        }

        .col-title {
            text-align: center;
            line-height: 1.3;
        }

        .header-line-sub {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-line-main {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .header-sekolah {
            font-size: 12px;
            font-weight: bold;
            margin-top: 1px;
        }

        .col-qr {
            width: 45px;
            text-align: center;
        }

        .col-qr .qr-placeholder {
            width: 30px;
            height: 30px;
            border: 1px solid #1a202c;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7px;
            font-weight: 700;
        }

        /* ── Body Kartu ── */
        .kartu-body {
            padding: 10px 12px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 11px;
            line-height: 1.3;
        }

        .info-table .label {
            width: 130px;
            font-weight: normal;
        }

        .info-table .sep {
            width: 12px;
            text-align: center;
        }

        .info-table .value {
            font-weight: bold;
        }

        .info-table .value.password {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0px;
        }

        .divider {
            border-top: 1px solid #e2e8f0;
            margin: 8px 0;
        }
    </style>

    <div class="preview-container">
        <div class="a4-mockup">
            <div style="text-align: center; margin-bottom: 25px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
                <h2 style="font-size: 16px; font-weight: 800; text-transform: uppercase;">Kartu Login Peserta TKA</h2>
                <p style="font-size: 13px; color: #666;">{{ $filterLabel }}</p>
            </div>

            <div class="kartu-grid">
                @foreach ($users as $user)
                @php
                    $jadwal = $user->jadwalTryouts->first();
                    $namaSesi = $jadwal->nama_sesi ?? '—';
                    $usernameProktor = '—';
                    
                    if ($jadwal) {
                        $penugasan = \App\Models\JadwalRuanganProktor::where('jadwal_tryout_id', $jadwal->id)
                            ->where('kelas_id', $user->kelas_id)
                            ->with('proktor')
                            ->first();
                        if ($penugasan && $penugasan->proktor) {
                            $usernameProktor = $penugasan->proktor->username;
                        }
                    }
                @endphp
                <div class="kartu">
                    <table class="kartu-header">
                        <tr>
                            <td class="col-logo">
                                <div class="logo-placeholder">LOGO</div>
                            </td>
                            <td class="col-title">
                                <div class="header-line-sub">KARTU LOGIN GLADI BERSIH</div>
                                <div class="header-line-main">TES KEMAMPUAN AKADEMIK</div>
                                <div class="header-sekolah">{{ $namaSekolah ?? '—' }}</div>
                                <div class="header-line-sub">TAHUN 2026</div>
                            </td>
                            <td class="col-qr">
                                <div class="qr-placeholder">QR</div>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="kartu-body">
                        @php
                            $ttl = '—';
                            if ($user->tempat_lahir || $user->tanggal_lahir) {
                                $tempat = $user->tempat_lahir ?: '—';
                                $tanggal = $user->tanggal_lahir ? $user->tanggal_lahir->translatedFormat('d F Y') : '—';
                                $ttl = "$tempat, $tanggal";
                            }
                        @endphp
                        <table class="info-table">
                            <tr>
                                <td class="label">Nama Peserta</td>
                                <td class="sep">:</td>
                                <td class="value">{{ $user->nama_lengkap ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="label">NISN</td>
                                <td class="sep">:</td>
                                <td class="value">{{ $user->nisn ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Tempat, Tanggal Lahir</td>
                                <td class="sep">:</td>
                                <td class="value">{{ $ttl }}</td>
                            </tr>
                            <tr>
                                <td class="label">Username</td>
                                <td class="sep">:</td>
                                <td class="value">{{ $user->username }}</td>
                            </tr>
                            <tr>
                                <td class="label">Password</td>
                                <td class="sep">:</td>
                                <td class="value password">{{ $user->plain_password }}</td>
                            </tr>
                            @if ($jenisCetak === 'dengan_jadwal')
                            <tr>
                                <td class="label">ID Proktor / Ruang</td>
                                <td class="sep">:</td>
                                <td class="value">{{ $usernameProktor }}</td>
                            </tr>
                            <tr>
                                <td class="label">Sesi</td>
                                <td class="sep">:</td>
                                <td class="value">{{ $namaSesi }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>
