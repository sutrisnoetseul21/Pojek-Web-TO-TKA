# 05. Student Portal — Implementasi Teknis

## A. Routing

```php
// routes/web.php (dalam middleware 'web')
Route::prefix('tryout')->group(function () {
    Route::get('/login',  [StudentController::class, 'login'])->name('tryout.login');
    Route::post('/login', [StudentController::class, 'authenticate'])->name('tryout.authenticate');

    Route::middleware('auth')->group(function () {
        Route::get('/biodata',            [StudentController::class, 'biodata'])->name('tryout.biodata');
        Route::post('/biodata',           [StudentController::class, 'storeBiodata'])->name('tryout.storeBiodata');
        Route::get('/konfirmasi/{jadwal}',[StudentController::class, 'konfirmasi'])->name('tryout.konfirmasi');
        Route::post('/mulai/{jadwal}',    [StudentController::class, 'mulai'])->name('tryout.mulai');
        Route::get('/soal/{pesertaJadwal}', [StudentController::class, 'soal'])->name('tryout.soal');
        Route::post('/jawab',             [StudentController::class, 'simpanJawaban'])->name('tryout.jawab');
        Route::get('/selesai/{pesertaJadwal}', [StudentController::class, 'selesai'])->name('tryout.selesai');
        Route::post('/submit/{pesertaJadwal}', [StudentController::class, 'submit'])->name('tryout.submit');
        Route::get('/hasil/{pesertaJadwal}',   [StudentController::class, 'hasil'])->name('tryout.hasil');
        Route::post('/logout',            [StudentController::class, 'logout'])->name('tryout.logout');
    });
});
```

---

## B. Controller Methods (`StudentController.php`)

| Method | HTTP | Fungsi |
|--------|------|--------|
| `login()` | GET | Form login peserta |
| `authenticate()` | POST | Validasi username/password |
| `biodata()` | GET | Form biodata + input token |
| `storeBiodata()` | POST | Simpan biodata, validasi token, link ke jadwal |
| `konfirmasi()` | GET | Info tryout sebelum mulai (nama, waktu, jumlah soal) |
| `mulai()` | POST | Set status `started`, `waktu_mulai`, redirect ke soal |
| `soal()` | GET | **Data utama**: kelompokkan soal per mapel via `mapelSections` |
| `simpanJawaban()` | POST | Simpan jawaban via AJAX (upsert ke `jawaban_peserta`) |
| `selesai()` | GET | Halaman konfirmasi selesai (ringkasan dijawab/ragu/kosong) |
| `submit()` | POST | Set status `completed`, hitung nilai, redirect ke hasil |
| `hasil()` | GET | Review jawaban + skor per soal |
| `logout()` | POST | Logout peserta |

### Method `soal()` — Detail

```php
// Menghasilkan mapelSections array:
$mapelSections = [
    [
        'nama_mapel' => 'Bahasa Indonesia',
        'waktu_menit' => 30,
        'soal' => [
            // Array soal dengan relasi jawaban & stimulus eager-loaded
            ['id' => 1, 'pertanyaan' => '...', 'tipe_soal' => 'PG_TUNGGAL',
             'jawaban' => [...], 'stimulus' => {...} ],
            ...
        ]
    ],
    [
        'nama_mapel' => 'Matematika',
        'waktu_menit' => 25,
        'soal' => [...]
    ],
];
```

---

## C. Views (Blade Templates)

```
resources/views/student/
├── layouts/
│   └── app.blade.php          # Layout utama (header, @yield, @stack)
├── login.blade.php            # Form login
├── biodata.blade.php          # Form biodata + token
├── konfirmasi.blade.php       # Info tryout sebelum mulai
├── soal.blade.php             # ⭐ Halaman mengerjakan (terbesar, ~700 lines)
├── selesai.blade.php          # Konfirmasi selesai + ringkasan
└── hasil.blade.php            # Review hasil + skor
```

---

## D. Halaman Soal (`soal.blade.php`) — Arsitektur JavaScript

### Data dari Server
```javascript
const pesertaJadwalId = {{ $pesertaJadwal->id }};
const mapelSections = @json($mapelSections);  // Array mapel + soal
const jawabanMap = @json($jawabanMap);         // Jawaban tersimpan {soalId: value}
const raguMap = @json($raguMap);               // Status ragu {soalId: true}
```

### State JavaScript
```javascript
let currentMapelIndex = 0;   // Index mapel aktif
let currentSoalIndex = 0;    // Index soal dalam mapel aktif
let answers = {};            // {soalId: jawaban}
let raguStatus = {};         // {soalId: boolean}
let mapelTimers = [];        // Sisa detik per mapel
let timerInterval = null;    // Interval timer
let pendingNextIndex = null; // Untuk two-step confirmation
```

### Fungsi Utama

| Fungsi | Kegunaan |
|--------|----------|
| `init()` | Load state dari server, mulai timer mapel pertama |
| `renderSoal()` | Render soal aktif (pertanyaan, opsi, stimulus) |
| `selectAnswer(soalId, jawId, tipeSoal)` | Handle klik jawaban + auto-save |
| `saveAnswer(soalId, value, tipeSoal)` | POST jawaban ke server via Fetch |
| `startMapelTimer()` | Mulai countdown timer mapel aktif |
| `updateTimerDisplay(seconds)` | Update tampilan timer (MM:SS) |
| `prevSoal()` / `nextSoal()` | Navigasi prev/next dalam mapel |
| `lanjutMapel()` | Trigger two-step confirmation |
| `showConfirmation(nextIndex)` | Tampilkan modal konfirmasi step 1 |
| `confirmStep2()` | Tampilkan modal konfirmasi step 2 |
| `finalConfirm()` | Tutup modal, tampilkan transition screen |
| `showTransition(nextIndex)` | Tampilkan transition screen antar mapel |
| `startNextMapel()` | Mulai mapel berikutnya (reset index, start timer) |
| `toggleRagu()` | Toggle ragu-ragu status + save ke server |
| `renderSoalGrid()` | Render grid nomor soal di modal daftar soal |
| `updateFooterButtons()` | Update tombol footer berdasarkan konteks |

### Alur Auto-Save

```
User klik jawaban → selectAnswer() → update answers[] state
                                    → saveAnswer() via Fetch API
                                    → POST /tryout/jawab
                                    → Controller upsert jawaban_peserta
```

---

## E. Two-Step Confirmation (Pindah Mapel)

Saat peserta klik "Lanjut Mapel Berikutnya":

**Step 1 (Warning):**
- Icon ⚠️, label "Konfirmasi 1 dari 2"
- Warning: "Setelah melanjutkan, Anda TIDAK DAPAT kembali ke soal mapel ini"
- Info mapel berikutnya (nama, jumlah soal, waktu)
- Info soal belum dijawab (jika ada, ditampilkan merah)
- Tombol: "Kembali ke Soal" / "Ya, Lanjutkan"

**Step 2 (Final):**
- Icon 🔒, label "Konfirmasi 2 dari 2"
- Pesan: "Soal pada mapel [nama] akan dikunci dan tidak bisa diakses lagi"
- Tombol: "← Kembali" / "Ya, Saya Yakin!" (warna merah)

**Setelah konfirmasi:** Transition screen dengan info mapel berikutnya + tombol "🚀 Mulai Mengerjakan".

---

## F. CSS Design System

Styling menggunakan **Vanilla CSS** (bukan Tailwind) dengan CSS variables:

```css
:root {
    --primary: #2563eb;
    --primary-dark: #1e40af;
    --primary-light: #93bbfd;
    --accent: #f59e0b;      /* Kuning (ragu) */
    --danger: #ef4444;       /* Merah (salah/selesai) */
    --success: #10b981;      /* Hijau (benar) */
}
```

Semua halaman student menggunakan inline `<style>` dalam blade (bukan file CSS terpisah).

---

## G. Catatan Teknis

### Eager Loading
`PaketTryoutMapel::getSoal()` harus eager-load `jawaban` dan `stimulus`:
```php
->with(['jawaban', 'stimulus'])->get();
```

### Jawaban Cast
`JawabanPeserta.jawaban` di-cast ke `array`:
- PG_TUNGGAL: string (ID jawaban tunggal)
- PG_KOMPLEKS: array (multiple IDs)

### View Cache
Setelah edit blade, jalankan `php artisan view:clear` jika error "Undefined variable" muncul.
