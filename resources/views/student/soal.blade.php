<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mengerjakan Soal - {{ $paket->nama_paket }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --secondary: #0ea5e9;
            --accent: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f3f4f6;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .header {
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 0.625rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo {
            width: 36px;
            height: 36px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .soal-nomor {
            font-weight: bold;
            font-size: 1rem;
        }

        .font-size-controls {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-left: 0.75rem;
        }

        .font-size-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 0.2rem 0.45rem;
            border-radius: 0.25rem;
            cursor: pointer;
            font-size: 0.75rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .timer {
            background: rgba(0, 0, 0, 0.2);
            padding: 0.4rem 0.75rem;
            border-radius: 0.5rem;
            font-family: monospace;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .timer.warning {
            background: var(--danger);
            animation: pulse 1s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .info-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 0.4rem 0.75rem;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .mapel-badge-header {
            background: rgba(255, 255, 255, 0.15);
            padding: 0.4rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
        }

        /* Mapel Banner */
        .mapel-banner {
            background: linear-gradient(90deg, #0f172a 0%, #1e3a5f 100%);
            color: white;
            text-align: center;
            padding: 0.6rem 1rem;
            flex-shrink: 0;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .mapel-banner .mapel-name {
            font-size: 1.05rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .mapel-banner .mapel-progress {
            font-size: 0.75rem;
            opacity: 0.7;
            font-weight: 400;
        }

        /* Main content */
        .main-content {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        .panel {
            flex: 1;
            overflow-y: auto;
            padding: 1.25rem;
        }

        .panel-stimulus {
            background: #fff;
            border-right: 3px solid #e5e7eb;
        }

        .panel-soal {
            background: #f9fafb;
        }

        .panel-content {
            background: white;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .stimulus-label {
            color: var(--primary);
            font-weight: 600;
            font-size: 0.8rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stimulus-content {
            line-height: 1.8;
            color: #374151;
        }

        .soal-content {
            line-height: 1.8;
            color: #374151;
            margin-bottom: 1.25rem;
        }

        .soal-instruction {
            background: #fef3c7;
            color: #92400e;
            padding: 0.625rem 0.875rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            margin-bottom: 0.875rem;
        }

        /* Options */
        .options {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }

        .option {
            display: flex;
            align-items: flex-start;
            gap: 0.875rem;
            padding: 0.875rem;
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .option:hover {
            border-color: var(--primary-light);
            background: #eff6ff;
        }

        .option.selected {
            border-color: var(--primary);
            background: #dbeafe;
        }

        .option input[type="checkbox"],
        .option input[type="radio"] {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            accent-color: var(--primary);
        }

        .option-label {
            font-weight: 600;
            color: var(--primary);
            min-width: 1.75rem;
        }

        .option-text {
            flex: 1;
            color: #374151;
        }

        /* Footer */
        .footer {
            background: white;
            border-top: 1px solid #e5e7eb;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .nav-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            font-size: 0.875rem;
        }

        .nav-btn-prev {
            background: var(--success);
            color: white;
        }

        .nav-btn-next {
            background: var(--primary);
            color: white;
        }

        .nav-btn-ragu {
            background: var(--accent);
            color: white;
        }

        .nav-btn-ragu.active {
            background: #d97706;
        }

        .nav-btn-selesai {
            background: var(--danger);
            color: white;
        }

        .nav-btn-lanjut-mapel {
            background: #7c3aed;
            color: white;
        }

        .nav-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .nav-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal {
            background: white;
            border-radius: 1rem;
            padding: 1.25rem;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #1f2937;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }

        .mapel-section-grid {
            margin-bottom: 1rem;
        }

        .mapel-section-title {
            font-size: 0.8rem;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .soal-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 0.4rem;
        }

        .soal-num {
            width: 100%;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e5e7eb;
            border-radius: 0.4rem;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .soal-num:hover {
            border-color: var(--primary);
        }

        .soal-num.current {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
        }

        .soal-num.answered {
            background: #dbeafe;
            border-color: var(--primary-light);
            color: var(--primary);
        }

        .soal-num.ragu {
            background: #fef3c7;
            border-color: var(--accent);
            color: #92400e;
        }

        .soal-num.locked {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .legend {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e5e7eb;
            font-size: 0.7rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .legend-box {
            width: 0.875rem;
            height: 0.875rem;
            border-radius: 0.2rem;
            border: 2px solid #e5e7eb;
        }

        .legend-box.answered {
            background: #dbeafe;
            border-color: var(--primary-light);
        }

        .legend-box.ragu {
            background: #fef3c7;
            border-color: var(--accent);
        }

        /* Confirmation Modal */
        .confirm-overlay {
            position: fixed;
            inset: 0;
            z-index: 300;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
        }

        .confirm-overlay.show {
            display: flex;
        }

        .confirm-modal {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            max-width: 420px;
            width: 90%;
            text-align: center;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .confirm-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .confirm-icon.warning {
            background: #fef3c7;
        }

        .confirm-icon.danger {
            background: #fee2e2;
        }

        .confirm-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.75rem;
        }

        .confirm-text {
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .confirm-warning {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 1.25rem;
            font-size: 0.8rem;
            color: #92400e;
            font-weight: 500;
        }

        .confirm-next-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 1.25rem;
            font-size: 0.85rem;
            color: #1e40af;
            font-weight: 500;
        }

        .confirm-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }

        .confirm-btn {
            padding: 0.75rem 1.75rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .confirm-btn:hover {
            transform: translateY(-1px);
        }

        .confirm-btn-cancel {
            background: #f3f4f6;
            color: #374151;
        }

        .confirm-btn-cancel:hover {
            background: #e5e7eb;
        }

        .confirm-btn-yes {
            background: var(--accent);
            color: white;
        }

        .confirm-btn-yes:hover {
            background: #d97706;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        }

        .confirm-btn-final {
            background: var(--danger);
            color: white;
        }

        .confirm-btn-final:hover {
            background: #dc2626;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .confirm-step-indicator {
            font-size: 0.7rem;
            color: #9ca3af;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Transition screen */
        .transition-screen {
            position: fixed;
            inset: 0;
            z-index: 200;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, var(--primary) 100%);
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: white;
            text-align: center;
        }

        .transition-screen.show {
            display: flex;
        }

        .transition-screen h2 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .transition-screen p {
            opacity: 0.8;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .transition-screen .btn-start {
            background: white;
            color: var(--primary);
            padding: 0.875rem 2.5rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .transition-screen .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .header {
                padding: 0.5rem 0.75rem;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .header-left {
                gap: 0.5rem;
            }

            .soal-nomor {
                font-size: 0.875rem;
            }

            .logo {
                width: 28px;
                height: 28px;
            }

            .timer {
                font-size: 0.9rem;
                padding: 0.25rem 0.5rem;
            }

            .mapel-badge-header {
                display: none;
            }

            .main-content {
                flex-direction: column;
                overflow-y: auto;
            }

            .panel {
                flex: none;
                overflow-y: visible;
                padding: 0.75rem;
            }

            .panel-stimulus {
                border-right: none;
                border-bottom: 3px solid #e5e7eb;
            }

            .panel-content {
                padding: 1rem;
            }

            .footer {
                padding: 0.5rem;
                gap: 0.375rem;
            }

            .nav-btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.75rem;
                flex: 1;
                justify-content: center;
            }

            .nav-btn span {
                display: none; /* Hide long button labels if any */
            }

            .modal {
                width: 95%;
                padding: 1rem;
            }

            .soal-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <div class="logo">
                <svg style="width:20px;height:20px;color:#1e40af;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3z" />
                </svg>
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <span class="soal-nomor">Soal <span id="currentNumber">1</span> / <span
                        id="totalSoalMapel">0</span></span>
                <div class="font-size-controls">
                    <button class="font-size-btn" onclick="changeFontSize(-1)">A-</button>
                    <button class="font-size-btn" onclick="changeFontSize(0)">A</button>
                    <button class="font-size-btn" onclick="changeFontSize(1)">A+</button>
                </div>
            </div>
        </div>
        <div class="header-right">
            <div class="user-info" style="text-align: right; margin-right: 0.5rem; display: flex; flex-direction: column;">
                <span style="font-size: 0.85rem; font-weight: 700;">{{ Auth::user()->nama_lengkap }}</span>
                <span style="font-size: 0.7rem; opacity: 0.8;">{{ Auth::user()->username }}</span>
            </div>
            <div class="timer" id="timer">00:00</div>
            <span class="mapel-badge-header">{{ $paket->nama_paket }}</span>
            <button class="info-btn" onclick="toggleDaftarSoal()">Daftar Soal</button>
        </div>
    </header>

    <!-- Mapel Banner -->
    <div class="mapel-banner">
        <span class="mapel-name" id="mapelName">-</span>
        <span class="mapel-progress" id="mapelProgress"></span>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Panel Stimulus -->
        <div class="panel panel-stimulus" id="panelStimulus" style="display:none;">
            <div class="panel-content">
                <div class="stimulus-label">
                    <svg style="width:14px;height:14px;" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
                    </svg>
                    WACANA / STIMULUS
                </div>
                <div class="stimulus-content" id="stimulusContent"></div>
            </div>
        </div>

        <!-- Panel Soal -->
        <div class="panel panel-soal">
            <div class="panel-content">
                <div class="soal-instruction" id="soalInstruction"></div>
                <div class="soal-content" id="soalContent"></div>
                <div class="options" id="optionsContainer"></div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <button class="nav-btn nav-btn-prev" id="btnPrev" onclick="prevSoal()">
            ‹ <span class="hidden md:inline">Soal sebelumnya</span><span class="md:hidden">Sblm</span>
        </button>

        <button class="nav-btn nav-btn-ragu" id="btnRagu" onclick="toggleRagu()">
            🚩 <span class="hidden md:inline">Ragu-ragu</span><span class="md:hidden">Ragu</span>
        </button>

        <button class="nav-btn nav-btn-next" id="btnNext" onclick="nextSoal()">
            <span class="hidden md:inline">Soal berikutnya</span><span class="md:hidden">Lanjt</span> ›
        </button>

        <button class="nav-btn nav-btn-lanjut-mapel" id="btnLanjutMapel" onclick="lanjutMapel()" style="display:none;">
            Lanjt Mapel ▸
        </button>

        <a class="nav-btn nav-btn-selesai" id="btnSelesai" href="{{ route('tryout.selesai', $pesertaJadwal) }}"
            style="display:none; text-decoration:none;">
            ✅ Selesai
        </a>
    </footer>

    <!-- Modal Daftar Soal -->
    <div class="modal-overlay" id="modalDaftarSoal" onclick="closeDaftarSoal(event)">
        <div class="modal" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3 class="modal-title">Daftar Soal</h3>
                <button class="modal-close" onclick="toggleDaftarSoal()">&times;</button>
            </div>
            <div id="soalGridContainer"></div>
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-box"></div><span>Belum</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box answered"></div><span>Dijawab</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box ragu"></div><span>Ragu</span>
                </div>
            </div>
            <div style="margin-top:1rem;text-align:center;">
                <a href="{{ route('tryout.selesai', $pesertaJadwal) }}" class="nav-btn nav-btn-selesai"
                    style="display:inline-flex;">
                    ✅ Selesai & Kumpulkan
                </a>
            </div>
        </div>
    </div>

    <!-- Two-step Confirmation Modal -->
    <div class="confirm-overlay" id="confirmOverlay">
        <div class="confirm-modal" id="confirmModal">
            <!-- Step 1 -->
            <div id="confirmStep1">
                <div class="confirm-step-indicator">Konfirmasi 1 dari 2</div>
                <div class="confirm-icon warning">⚠️</div>
                <div class="confirm-title">Lanjut ke Mapel Berikutnya?</div>
                <div class="confirm-warning">
                    ⛔ Setelah melanjutkan, Anda <b>TIDAK DAPAT kembali</b> ke soal mapel ini.
                    Pastikan semua jawaban sudah benar!
                </div>
                <div class="confirm-next-info" id="confirmNextInfo"></div>
                <div class="confirm-text" id="confirmUnanswered"></div>
                <div class="confirm-actions">
                    <button class="confirm-btn confirm-btn-cancel" onclick="closeConfirm()">Kembali ke Soal</button>
                    <button class="confirm-btn confirm-btn-yes" onclick="confirmStep2()">Ya, Lanjutkan</button>
                </div>
            </div>
            <!-- Step 2 -->
            <div id="confirmStep2" style="display:none;">
                <div class="confirm-step-indicator">Konfirmasi 2 dari 2</div>
                <div class="confirm-icon danger">🔒</div>
                <div class="confirm-title">Apakah Anda Yakin?</div>
                <div class="confirm-text">
                    Ini adalah konfirmasi terakhir.<br>
                    Soal pada mapel <b id="confirmMapelName"></b> akan <b>dikunci</b> dan tidak bisa diakses lagi.
                </div>
                <div class="confirm-actions">
                    <button class="confirm-btn confirm-btn-cancel" onclick="backToStep1()">← Kembali</button>
                    <button class="confirm-btn confirm-btn-final" onclick="finalConfirm()">Ya, Saya Yakin!</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transition screen between mapels -->
    <div class="transition-screen" id="transitionScreen">
        <div style="font-size:3rem;margin-bottom:1rem;">📚</div>
        <h2 id="transitionTitle">Mata Pelajaran Berikutnya</h2>
        <p id="transitionInfo"></p>
        <button class="btn-start" id="transitionBtn" onclick="startNextMapel()">🚀 Mulai Mengerjakan</button>
    </div>

    <script>
        // Data from server
        const pesertaJadwalId = {{ $pesertaJadwal->id }};
        const mapelSections = @json($mapelSections);
        const jawabanMap = @json($jawabanMap);
        const raguMap = @json($raguMap);

        let currentMapelIndex = 0;
        let currentSoalIndex = 0;
        let answers = {};
        let raguStatus = {};
        let mapelTimers = []; // remaining seconds per mapel
        let timerInterval = null;

        // Initialize
        (function init() {
            // Convert jawaban/ragu maps
            for (const [k, v] of Object.entries(jawabanMap)) answers[k] = v;
            for (const [k, v] of Object.entries(raguMap)) raguStatus[k] = v;

            // Setup per-mapel timers
            mapelSections.forEach((section, i) => {
                mapelTimers[i] = section.waktu_menit * 60;
            });

            // Start first mapel
            startMapelTimer();
            renderSoal();
        })();

        // Timer
        function startMapelTimer() {
            if (timerInterval) clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                mapelTimers[currentMapelIndex]--;

                if (mapelTimers[currentMapelIndex] <= 0) {
                    mapelTimers[currentMapelIndex] = 0;
                    clearInterval(timerInterval);
                    // Auto move to next mapel
                    if (currentMapelIndex < mapelSections.length - 1) {
                        showTransition(currentMapelIndex + 1);
                    } else {
                        // Last mapel done → go to selesai
                        window.location.href = "{{ route('tryout.selesai', $pesertaJadwal) }}";
                    }
                }

                updateTimerDisplay();
            }, 1000);

            updateTimerDisplay();
        }

        function updateTimerDisplay() {
            const secs = Math.max(0, mapelTimers[currentMapelIndex]);
            const m = Math.floor(secs / 60);
            const s = secs % 60;
            const timerEl = document.getElementById('timer');
            timerEl.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            timerEl.classList.toggle('warning', secs < 60);
        }

        // Render current soal
        function renderSoal() {
            const section = mapelSections[currentMapelIndex];
            const soal = section.soal[currentSoalIndex];

            // Mapel banner
            document.getElementById('mapelName').textContent = section.nama_mapel;
            document.getElementById('mapelProgress').textContent =
                `Mapel ${currentMapelIndex + 1} dari ${mapelSections.length}`;

            // Soal number
            document.getElementById('currentNumber').textContent = currentSoalIndex + 1;
            document.getElementById('totalSoalMapel').textContent = section.soal.length;

            // Pertanyaan
            document.getElementById('soalContent').innerHTML = soal.pertanyaan || '';

            // Stimulus
            const panelStimulus = document.getElementById('panelStimulus');
            if (soal.stimulus && soal.stimulus.konten) {
                panelStimulus.style.display = 'block';
                document.getElementById('stimulusContent').innerHTML = soal.stimulus.konten;
            } else {
                panelStimulus.style.display = 'none';
            }

            // Instruction
            const instructions = {
                'PG_TUNGGAL': 'Pilihlah satu jawaban yang paling tepat!',
                'PG_KOMPLEKS': 'Pilihlah lebih dari satu jawaban yang benar!',
                'BENAR_SALAH': 'Tentukan pernyataan benar atau salah!',
                'MENJODOHKAN': 'Jodohkanlah dengan tepat!',
                'ISIAN': 'Isilah jawaban dengan tepat!',
                'URAIAN': 'Jawablah dengan uraian lengkap!'
            };
            document.getElementById('soalInstruction').textContent = instructions[soal.tipe_soal] || '';

            // Options
            const container = document.getElementById('optionsContainer');
            container.innerHTML = '';

            if (soal.tipe_soal === 'BENAR_SALAH') {
                const table = document.createElement('table');
                table.style.width = '100%';
                table.style.borderCollapse = 'collapse';
                table.innerHTML = `
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb; text-align: left;">
                            <th style="padding: 0.75rem;">Pernyataan</th>
                            <th style="padding: 0.75rem; width: 100px; text-align: center;">Benar</th>
                            <th style="padding: 0.75rem; width: 100px; text-align: center;">Salah</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                `;
                const tbody = table.querySelector('tbody');

                if (soal.jawaban) {
                    soal.jawaban.forEach((opsi, idx) => {
                        const tr = document.createElement('tr');
                        tr.style.borderBottom = '1px solid #f3f4f6';

                        const currentAnswer = (answers[soal.id] && typeof answers[soal.id] === 'object')
                            ? answers[soal.id][opsi.id]
                            : null;

                        tr.innerHTML = `
                            <td style="padding: 0.75rem;">${opsi.teks_jawaban || '-'}</td>
                            <td style="padding: 0.75rem; text-align: center;">
                                <input type="radio" name="bs_${soal.id}_${opsi.id}" value="BENAR" 
                                    ${currentAnswer === 'BENAR' ? 'checked' : ''}
                                    style="width: 18px; height: 18px; accent-color: var(--success); cursor: pointer;">
                            </td>
                            <td style="padding: 0.75rem; text-align: center;">
                                <input type="radio" name="bs_${soal.id}_${opsi.id}" value="SALAH" 
                                    ${currentAnswer === 'SALAH' ? 'checked' : ''}
                                    style="width: 18px; height: 18px; accent-color: var(--danger); cursor: pointer;">
                            </td>
                        `;

                        // Add event listeners
                        const radios = tr.querySelectorAll('input[type="radio"]');
                        radios.forEach(radio => {
                            radio.addEventListener('change', (e) => {
                                saveAnswer(soal.id, e.target.value, 'BENAR_SALAH', opsi.id);
                            });
                        });

                        tbody.appendChild(tr);
                    });
                }
                container.appendChild(table);

            } else if (soal.jawaban && soal.jawaban.length > 0) {
                soal.jawaban.forEach((opsi, idx) => {
                    const isSelected = answers[soal.id] == opsi.id ||
                        (Array.isArray(answers[soal.id]) && answers[soal.id].includes(String(opsi.id)));

                    const inputType = soal.tipe_soal === 'PG_KOMPLEKS' ? 'checkbox' : 'radio';
                    const labels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

                    const el = document.createElement('label');
                    el.className = 'option' + (isSelected ? ' selected' : '');
                    el.innerHTML = `
                        <input type="${inputType}" name="jawaban" value="${opsi.id}" ${isSelected ? 'checked' : ''}>
                        <span class="option-label">${labels[idx] || idx + 1}</span>
                        <span class="option-text">${opsi.teks_jawaban || ''}</span>
                    `;

                    el.querySelector('input').addEventListener('change', e => {
                        saveAnswer(soal.id, e.target.value, soal.tipe_soal);
                    });

                    container.appendChild(el);
                });
            }

            // Ragu button
            const btnRagu = document.getElementById('btnRagu');
            btnRagu.classList.toggle('active', !!raguStatus[soal.id]);

            // Navigation buttons
            const isFirstSoal = currentSoalIndex === 0;
            const isLastSoal = currentSoalIndex === section.soal.length - 1;
            const isLastMapel = currentMapelIndex === mapelSections.length - 1;

            document.getElementById('btnPrev').disabled = isFirstSoal;
            document.getElementById('btnPrev').style.visibility = isFirstSoal ? 'hidden' : 'visible';

            if (isLastSoal && isLastMapel) {
                // Very last question → show Selesai
                document.getElementById('btnNext').style.display = 'none';
                document.getElementById('btnLanjutMapel').style.display = 'none';
                document.getElementById('btnSelesai').style.display = 'inline-flex';
            } else if (isLastSoal && !isLastMapel) {
                // Last of this mapel → show Lanjut Mapel
                document.getElementById('btnNext').style.display = 'none';
                document.getElementById('btnLanjutMapel').style.display = 'inline-flex';
                document.getElementById('btnSelesai').style.display = 'none';
            } else {
                // Normal → show Next
                document.getElementById('btnNext').style.display = 'inline-flex';
                document.getElementById('btnLanjutMapel').style.display = 'none';
                document.getElementById('btnSelesai').style.display = 'none';
            }

            // Update grid
            renderSoalGrid();
        }

        // Save answer
        function saveAnswer(soalId, value, tipeSoal, opsiId = null) {
            if (tipeSoal === 'PG_KOMPLEKS') {
                if (!Array.isArray(answers[soalId])) answers[soalId] = [];
                const idx = answers[soalId].indexOf(value);
                if (idx > -1) answers[soalId].splice(idx, 1);
                else answers[soalId].push(value);
            } else if (tipeSoal === 'BENAR_SALAH') {
                if (!answers[soalId] || typeof answers[soalId] !== 'object' || Array.isArray(answers[soalId])) {
                    answers[soalId] = {};
                }
                // Jika opsiId ada (param ke-4), simpan per key
                if (opsiId) {
                    answers[soalId][opsiId] = value;
                }
            } else {
                answers[soalId] = value;
            }

            fetch("{{ route('tryout.jawab') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    peserta_jadwal_id: pesertaJadwalId,
                    bank_soal_id: soalId,
                    jawaban: answers[soalId]
                })
            });

            renderSoal();
        }

        // Toggle ragu
        function toggleRagu() {
            const soal = mapelSections[currentMapelIndex].soal[currentSoalIndex];
            raguStatus[soal.id] = !raguStatus[soal.id];
            renderSoal();
        }

        // Navigation
        function prevSoal() {
            if (currentSoalIndex > 0) { currentSoalIndex--; renderSoal(); }
        }

        function nextSoal() {
            const section = mapelSections[currentMapelIndex];
            if (currentSoalIndex < section.soal.length - 1) { currentSoalIndex++; renderSoal(); }
        }

        let pendingNextIndex = null;

        function lanjutMapel() {
            if (currentMapelIndex < mapelSections.length - 1) {
                showConfirmation(currentMapelIndex + 1);
            }
        }

        // Two-step confirmation
        function showConfirmation(nextIndex) {
            pendingNextIndex = nextIndex;
            const currentSection = mapelSections[currentMapelIndex];
            const nextSection = mapelSections[nextIndex];

            // Count unanswered
            let unanswered = 0;
            currentSection.soal.forEach(s => { if (!answers[s.id]) unanswered++; });

            document.getElementById('confirmNextInfo').innerHTML =
                '📖 Mapel berikutnya: <b>' + nextSection.nama_mapel + '</b><br>' +
                nextSection.soal.length + ' soal • ' + nextSection.waktu_menit + ' menit';

            if (unanswered > 0) {
                document.getElementById('confirmUnanswered').innerHTML =
                    '⚡ Anda masih memiliki <b style="color:#ef4444">' + unanswered +
                    ' soal belum dijawab</b> pada mapel <b>' + currentSection.nama_mapel + '</b>.';
            } else {
                document.getElementById('confirmUnanswered').innerHTML =
                    '✅ Semua soal pada mapel <b>' + currentSection.nama_mapel + '</b> sudah dijawab.';
            }

            document.getElementById('confirmMapelName').textContent = currentSection.nama_mapel;

            // Show step 1
            document.getElementById('confirmStep1').style.display = 'block';
            document.getElementById('confirmStep2').style.display = 'none';
            document.getElementById('confirmOverlay').classList.add('show');
        }

        function confirmStep2() {
            document.getElementById('confirmStep1').style.display = 'none';
            document.getElementById('confirmStep2').style.display = 'block';
        }

        function backToStep1() {
            document.getElementById('confirmStep2').style.display = 'none';
            document.getElementById('confirmStep1').style.display = 'block';
        }

        function closeConfirm() {
            document.getElementById('confirmOverlay').classList.remove('show');
            pendingNextIndex = null;
        }

        function finalConfirm() {
            document.getElementById('confirmOverlay').classList.remove('show');
            // Show transition screen
            showTransition(pendingNextIndex);
        }

        // Transition screen
        function showTransition(nextIndex) {
            clearInterval(timerInterval);
            const nextSection = mapelSections[nextIndex];
            document.getElementById('transitionTitle').textContent = nextSection.nama_mapel;
            document.getElementById('transitionInfo').textContent =
                nextSection.soal.length + ' soal  •  ' + nextSection.waktu_menit + ' menit';
            document.getElementById('transitionScreen').classList.add('show');
            document.getElementById('transitionScreen').dataset.nextIndex = nextIndex;
        }

        function startNextMapel() {
            const nextIndex = parseInt(document.getElementById('transitionScreen').dataset.nextIndex);
            document.getElementById('transitionScreen').classList.remove('show');
            currentMapelIndex = nextIndex;
            currentSoalIndex = 0;
            startMapelTimer();
            renderSoal();
        }

        function goToSoal(mapelIdx, soalIdx) {
            if (mapelIdx !== currentMapelIndex) return; // can only navigate in current mapel
            currentSoalIndex = soalIdx;
            renderSoal();
            toggleDaftarSoal();
        }

        // Render soal grid (grouped per mapel)
        function renderSoalGrid() {
            const container = document.getElementById('soalGridContainer');
            container.innerHTML = '';

            mapelSections.forEach((section, mi) => {
                const div = document.createElement('div');
                div.className = 'mapel-section-grid';

                const title = document.createElement('div');
                title.className = 'mapel-section-title';
                title.textContent = section.nama_mapel + (mi === currentMapelIndex ? ' (Aktif)' : '');
                div.appendChild(title);

                const grid = document.createElement('div');
                grid.className = 'soal-grid';

                section.soal.forEach((soal, si) => {
                    const num = document.createElement('div');
                    num.className = 'soal-num';
                    num.textContent = si + 1;

                    if (mi === currentMapelIndex && si === currentSoalIndex) {
                        num.classList.add('current');
                    } else if (raguStatus[soal.id]) {
                        num.classList.add('ragu');
                    } else if (answers[soal.id]) {
                        num.classList.add('answered');
                    }

                    if (mi !== currentMapelIndex) {
                        num.classList.add('locked');
                    } else {
                        num.onclick = () => goToSoal(mi, si);
                    }

                    grid.appendChild(num);
                });

                div.appendChild(grid);
                container.appendChild(div);
            });
        }

        // Modal
        function toggleDaftarSoal() {
            document.getElementById('modalDaftarSoal').classList.toggle('show');
        }
        function closeDaftarSoal(event) {
            if (event.target === event.currentTarget) toggleDaftarSoal();
        }

        // Font size
        let fontSize = 16;
        function changeFontSize(delta) {
            fontSize = delta === 0 ? 16 : Math.max(12, Math.min(24, fontSize + delta * 2));
            document.getElementById('soalContent').style.fontSize = fontSize + 'px';
            document.getElementById('stimulusContent').style.fontSize = fontSize + 'px';
        }
    </script>
</body>

</html>