<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Login Peserta Tryout</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }

        h1.page-title {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }

        .kartu-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
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

        @media print {
            body {
                background: white;
                padding: 8px;
            }

            h1.page-title {
                font-size: 14px;
                margin-bottom: 12px;
            }

            .kartu-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
            }

            @page {
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    <h1 class="page-title">
        📋 Kartu Login Peserta Tryout &mdash; {{ $filterLabel ?? 'Semua Peserta' }}<br>
        <small style="font-size:12px; font-weight:400; color:#718096;">Dicetak Tgl. {{ now()->format('d F Y H:i') }}</small>
    </h1>

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
                    <div class="field-value sekolah">{{ $user->sekolah ?: '—' }}</div>
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

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
