# Implementation Plan - Dashboard Admin Widgets (Filament) - REVISED

Tujuan: Membuat dan memasang 3 jenis Filament Widgets di halaman Dashboard utama Admin untuk memonitoring aktivitas tryout secara realtime serta melihat statistik & tren dengan praktik terbaik (Eager Loading & Proper Sorting).

---

## 📅 Daftar Widget & Spesifikasi

### 1. `StatsOverviewWidget` (Baris Atas - 4 Kolom)
**Class**: `App\Filament\Widgets\DashboardStatsOverview`

*   **Stat 1: Peserta Sedang Ujian**
    *   **Query**: `PesertaJadwal::where('status', 'started')->count()`
    *   **Style**: Color `success`, Icon `heroicon-m-bolt`.
*   **Stat 2: Total Peserta**
    *   **Query**: `User::where('role', 'peserta')->count()`
*   **Stat 3: Tryout Aktif Hari Ini**
    *   **Query**: `JadwalTryout::where('is_active', true)->whereDate('tgl_mulai', '<=', today())->whereDate('tgl_selesai', '>=', today())->count()`
*   **Stat 4: Total Bank Soal**
    *   **Query**: `BankSoal::count()`
*   **Urutan (Sort)**: `protected static ?int $sort = 1;`
*   **Polling**: Auto-refresh `10s`.

---

### 2. `TableWidget` (Baris Tengah - Live Monitoring)
**Class**: `App\Filament\Widgets\LiveMonitoringTable`

*   **Tujuan**: Menampilkan peserta yang sedang aktif mengerjakan soal.
*   **Sumber Data**: `PesertaJadwal::where('status', 'started')->with(['user.kelas', 'jadwalTryout'])`
    *   > [!IMPORTANT]
    *   Eager loading `with([user.kelas, jadwalTryout])` wajib dilakukan untuk mencegah N+1 query issue karena polling berjalan sangat rapat.
*   **Kolom yang Ditampilkan**:
    *   `user.name` (Nama Peserta)
    *   `user.kelas.nama_kelas` (Kelas) -> *Casing fallback "Tidak Ada Kelas" jika empty.*
    *   `jadwalTryout.nama_sesi` (Sesi / Ujian)
    *   `status` (Status: `started`)
*   **Fitur**:
    *   Pagination: 5 Baris.
    *   Search/Filter: Disabled (agar data fetching super ringan).
    *   Urutan (Sort): `protected static ?int $sort = 2;`
    *   Span: `protected int | string | array $columnSpan = 'full';`
    *   Poll: `5s`.

---

### 3. `ChartWidget` (Baris Bawah - Tren Partisipasi)
**Class**: `App\Filament\Widgets\PartisipasiChart`

*   **Tipe**: Line Chart.
*   **Tujuan**: Menampilkan tren peserta submit/selesai (`completed`) dalam 7 hari terakhir.
*   **Sumbu X**: Tanggal (7 hari lalu s/d hari ini).
*   **Sumbu Y**: `count()` data `PesertaJadwal`.
*   **Metode**: Menggunakan standard Carbon aggregates & group by date untuk kebebasan query murni (atau package `laravel-trend` jika Anda menyukainya, namun standard DB query pun mudah: `DATE(waktu_selesai)`).
*   **Urutan (Sort)**: `protected static ?int $sort = 3;`

---

## 🛠️ Langkah Perubahan Kode

### 1. PEMBUATAN FILE WIDGET [NEW]
Kami akan membuat 3 file di dalam direktori `app/Filament/Widgets/`:
1.  `DashboardStatsOverview.php`
2.  `LiveMonitoringTable.php`
3.  `PartisipasiChart.php`

### 2. SISTEM REGISTRASI [NO CHANGES IN PROVIDER]
Filament v3 mendeteksi file di `app/Filament/Widgets` via `discoverWidgets()`.
Kita **tidak perlu** menambahkannya ke setup `->widgets([])` di provider agar terhindar dari bias Double-Render. Urutan & display full span telah diatur dari model internal widget class.

---

## ✅ Rencana Verifikasi (Manual)

Untuk memvalidasi widget bekerja, silakan buka Dashboard Admin (`/admin`):

1.  **Verifikasi Stats**:
    *   Cek apakah stat "Peserta Sedang Ujian" bertambah saat ada peserta yang mengeklik "Mulai".
2.  **Verifikasi Tabel**:
    *   Pastikan nama peserta, kelas, dan mapelnya termuat dengan efisien tanpa lag (Polling 5s).
3.  **Verifikasi Chart**:
    *   Grafik termuat dengan garis tren dinamis di 7 hari kalender.
