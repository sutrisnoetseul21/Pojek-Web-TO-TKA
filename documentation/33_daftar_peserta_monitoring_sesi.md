# 33. Halaman Daftar Peserta Monitoring Sesi

Halaman ini digunakan oleh proktor untuk memonitor daftar peserta yang sedang/akan mengikuti ujian sesi aktif pada hari ini.

## 📌 Deskripsi Fitur
Menampilkan tabel data peserta dari jadwal tryout yang statusnya **aktif** hari ini, dilengkapi filter dropdown kelompok sesi. Dilengkapi dengan design interface custom card filter diatas data table.

---

## 🛠️ Detail Implementasi

### 1. Model & Query Utama
- **Model**: `App\Models\PesertaJadwal`
- **Query Dasar**:
  ```php
  PesertaJadwal::whereIn('jadwal_tryout_id', $activeJadwalIds)
  ```
  `$activeJadwalIds` diambil dari `JadwalTryout` yang `is_active = true` dan tanggalnya adalah **hari ini** (`now()`).

### 2. Layout & Filter
Layout page ini menggunakan custom view (`daftar-peserta.blade.php`) di atas tabel:
- **Panel Filter**:
  - Select / Dropdown **Kelompok/Kelas**: Menampilkan `nama_sesi` yang unik dari kumpulan `JadwalTryout` aktif hari ini.
  - Pilihan **Semua Kelas** (Default / `all`) untuk melihat semua list peserta jadwal aktif tanpa filter sesi tertentu.
  - Tombol **Apply** untuk memicu reload render.

---

## 📊 Kolom Tabel
| Label Kolom | State Sumber |
| :--- | :--- |
| **No** | Row Index |
| **Username** | `user.username` |
| **Nama** | `user.nama_lengkap` |
| **Kelompok** | `jadwalTryout.nama_sesi` |
| **NIK/No. Peserta** | `user.nomor_peserta` |

---

## 📂 File Terkait
- `app/Filament/Pages/DaftarPeserta.php`
- `resources/views/filament/pages/daftar-peserta.blade.php`
- `app/Models/PesertaJadwal.php`
