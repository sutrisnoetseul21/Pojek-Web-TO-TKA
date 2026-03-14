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
            /* Sembunyikan elemen Filament saat print */
            .fi-header, .fi-sidebar, .fi-topbar, .fi-actions {
                display: none !important;
            }
        }

        /* Styles dari kartu-peserta.blade.php */
        .kartu-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .kartu {
            background: white;
            border: 1.5px solid #cbd5e0;
            border-radius: 10px;
            overflow: hidden;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .kartu-header {
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
            color: white;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .kartu-header .logo {
            width: 28px;
            height: 28px;
            background: rgba(255,255,255,0.2);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .kartu-header .title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .kartu-header .subtitle {
            font-size: 9px;
            opacity: 0.75;
            margin-top: 1px;
        }

        .kartu-body {
            padding: 12px 14px;
        }

        .field-group {
            margin-bottom: 8px;
        }

        .field-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #718096;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .field-value {
            font-size: 13px;
            font-weight: 700;
            color: #1a202c;
            line-height: 1.3;
        }

        .field-value.sekolah {
            font-size: 11px;
            font-weight: 600;
            color: #4a5568;
        }

        .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 10px 0;
        }

        .credentials-box {
            background: #f7fafc;
            border: 1px dashed #cbd5e0;
            border-radius: 6px;
            padding: 8px 12px;
        }

        .credential-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .credential-row:last-child {
            margin-bottom: 0;
        }

        .credential-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #718096;
            font-weight: 600;
            width: 60px;
            flex-shrink: 0;
        }

        .credential-value {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            font-weight: 800;
            color: #1a202c;
            letter-spacing: 1px;
        }

        .credential-value.password {
            color: #c53030;
            background: #fff5f5;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid #fed7d7;
        }
    </style>

    <div class="preview-container">
        <div class="a4-mockup">
            <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px;">
                <h2 style="font-size: 18px; font-weight: 800; text-transform: uppercase;">Kartu Login Peserta Tryout</h2>
                <p style="font-size: 14px; color: #666;">{{ $filterLabel }}</p>
            </div>

            <div class="kartu-grid">
                @foreach ($users as $user)
                <div class="kartu">
                    <div class="kartu-header">
                        <div class="logo">🎓</div>
                        <div>
                            <div class="title">Kartu Peserta</div>
                            <div class="subtitle">Bimbel Excellent</div>
                        </div>
                    </div>
                    <div class="kartu-body">
                        <div class="field-group">
                            <div class="field-label">Nama Lengkap</div>
                            <div class="field-value">{{ $user->nama_lengkap ?: '—' }}</div>
                        </div>
                        <div class="field-group">
                            <div class="field-label">Sekolah</div>
                            <div class="field-value sekolah">{{ $user->sekolahRelation->nama_sekolah ?? '—' }}</div>
                        </div>
                        <div class="divider"></div>
                        <div class="credentials-box">
                            <div class="credential-row">
                                <div class="credential-label">Username</div>
                                <div class="credential-value">{{ $user->username }}</div>
                            </div>
                            <div class="credential-row">
                                <div class="credential-label">Password</div>
                                <div class="credential-value password">{{ $user->plain_password }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>
