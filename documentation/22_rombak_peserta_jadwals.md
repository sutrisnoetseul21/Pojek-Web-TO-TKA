# Implementation Plan - Rombak Monitoring Peserta Ujian

Tujuan: Menyempurnakan halaman Monitoring Peserta (`PesertaJadwalResource`) agar navigasinya lebih nyaman bagi Admin Sekolah, fungsionalitas seleksi lebih terarah, dan memastikan seluruh peserta termonitoring dengan baik.

---

## 📅 Daftar Masalah & Solusi

### 1. Filter Sekolah Tumpang Tindih (Point 1)
*   **Masalah**: Admin biasa melihat Filter Sekolah keseluruhan, padahal data tabel sudah otomatis ter-scope ke sekolah mereka di `getEloquentQuery()`.
*   **Solusi**:
    *   Hanya menampilkan `Tables\Filters\SelectFilter::make('sekolah')` jika diakses oleh **Super Admin**.
    *   Menggunakan: `->visible(fn () => auth()->user()->hasRole('super_admin'))` pada filter Sekolah.

---

### 2. Bingung Data Membludak Multi-Jadwal (Point 2)
*   **Masalah**: Admin bingung jika 3 jadwal aktif digabung dalam satu list tabel tanpa seleksi di awal.
*   **Solusi**: Mengimplementasikan **Table Tabs** di bagian atas tabel.
    *   Tabs memisahkan baris berdasarkan `nama_sesi` (Jadwal).
    *   Admin dapat mengklik Tab "Ujian Matematika" untuk memantau Ujian Matematika saja, murni dan fokus.
    *   Implementasi akan diletakkan di file index page: `ManagePesertaJadwals.php` via `getTabs()`.

---

### 3. Peserta yang Belum Tes Tidak Muncul (Point 3)
*   **Masalah**: Pengguna membuat banyak akun, tapi yang belum mengerjakan tidak nampak di tabel.
*   **Analisis**:
    *   Status `registered` (Belum Mulai) seharusnya terlihat.
    *   Jika akun tidak muncul sama sekali, kemungkinan dikarenakan row `peserta_jadwal` belum terbentuk (sinkronisasi manual terlewat).
*   **Solusi**:
    *   Menambahkan **Header Action** baru bernama **`Sinkronisasi Peserta`** di `PesertaJadwalResource`.
    *   Menjalankan ulang logic query mapping: Ambil semua user di kelas yang bersangkutan, buat `PesertaJadwal` record jika belum ada dengan status `registered`.
    *   Dengan ini, admin bisa memastikan row list seluruh peserta siap dipantau.

---

## 🛠️ Langkah Perubahan Kode

### 1. Modifikasi [MODIFY] `PesertaJadwalResource.php`
*   Scope `.visible()` pada filter Sekolah.
*   Menambah model static sync Action jika diperlukan di list view Header, atau trigger refresh button template.

### 2. Modifikasi [MODIFY] `ManagePesertaJadwals.php`
*   Tambahkan method `getTabs()`:
```php
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

public function getTabs(): array
{
    $tabs = ['all' => Tab::make('Semua Sesi')];

    // Ambil sesi aktif 5 jam ke belakang/depan
    $jadwals = \App\Models\JadwalTryout::where('is_active', true)
        ->whereDate('tgl_selesai', '>=', now()->subHours(5))
        ->get();

    foreach ($jadwals as $jadwal) {
        $tabs[$jadwal->id] = Tab::make($jadwal->nama_sesi)
            ->modifyQueryUsing(fn (Builder $query) => $query->where('jadwal_tryout_id', $jadwal->id));
    }

    return $tabs;
}
```

---

## ✅ Rencana Verifikasi (Manual)

Untuk memvalidasi perombakan ini:

1.  **Filter Sekolah**: Login sebagai admin biasa, pastikan filter sekolah tidak membebani filter sidebar.
2.  **Dashboard Tabs**: Cek apakah deretan Tab Sesi Ujian muncul di atas tabel monitoring, dan saat diklik langsung memfilter list.
3.  **Seluruh Peserta**: Pastikan row dengan label "Belum Mulai" nampak jika filter tab mengarah ke jadwal yang relevan secara eksklusif.
