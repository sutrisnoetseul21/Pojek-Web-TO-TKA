# Perbaikan Bug Timer & Sinkronisasi Mapel (Multi-Subject)

Dokumentasi ini mencatat rangkuman bug yang ditemukan pada fitur Timer Ujian serta langkah perbaikan yang telah diterapkan untuk memastikan sinkronisasi waktu antara siswa dan admin berjalan akurat.

---

## 🚨 Masalah yang Ditemukan

1. **Timer Berjumlah 1 Jam (Seharusnya 30 Menit)**:
   - *Penyebab*: Kode lama menjumlahkan semua durasi sub-tes (`30 + 30 = 60 menit`) pada saat inisialisasi awal `sisa_waktu` di database.
2. **Sisa Waktu Statis di Admin Panel**:
   - *Penyebab*: Komunikasi pengiriman sisa waktu dari browser siswa hanya terpicu saat murid meng-klik pilihan ganda. Jika murid diam membaca soal, database tidak ter-update.
3. **Hilangnya Posisi Mapel Pasca Reset Login**:
   - *Penyebab*: Browser siswa mengandalkan `localStorage` untuk mengetahui sub-tes yang sedang dibuka. Saat Admin melakukan Reset Sesi, data lokal ini hilang/kosong sehingga murid kembali dilempar ke urutan mapel paling awal (indeks 0).

---

## 🛠️ Langkah Perbaikan yang Diterapkan

### 1. Perbaikan Start Timer (Durasi Tunggal)
- **File**: `app/Http/Controllers/StudentController.php` (Fungsi `mulai()`)
- **Tindakan**: Menghapus logika penjumlahan total menit. Sekarang inisialisasi `sisa_waktu` diatur murni mengambil durasi dari mapel indeks pertama saja (`$firstMapel->waktu_mapel * 60` detik).

### 2. Penambahan Live Heartbeat (Detak Jantung Waktu)
- **File Controller**: `app/Http/Controllers/StudentController.php` (Fungsi `syncWaktu()`)
- **File Rute**: `routes/student.php` (`/sync-waktu`)
- **File Blade**: `resources/views/student/soal.blade.php`
- **Tindakan**: Menambahkan `setInterval` background fetch di halaman siswa yang mengirimkan data `sisa_waktu` per **20 detik** secara senyap, tanpa mengganggu pengerjaan siswa.

### 3. Preservasi Mapel Berbasis Server (Fallback)
- **File**: `resources/views/student/soal.blade.php` (Fungsi `init()`)
- **Tindakan**: Mengubah pencarian indeks mapel. Jika `localStorage` kosong, client akan mencocokkan `mapelSections` dengan `$pesertaJadwal->current_mapel_id` dari server untuk memulihkan posisi pengerjaan asli.

### 4. Optimalisasi Polling & Format Menit Panel Admin
- **File**: `app/Filament/Pages/StatusPeserta.php` & `DaftarLogin.php`
- **Tindakan**: 
  - Mengubah tampilan detik (`00:29:55`) menjadi satuan pembulatan menit (`30m`, `29m`) menggunakan `ceil($state / 60)` untuk menyembunyikan jitter kecil.
  - Membatasi kueri `->poll('20s')` pada tabel monitoring agar ramah kinerja server backend.

---

## ✅ Hasil Pengujian
Sistem bekerja responsif:
- Sesi ujian dimulai tepat pada menit individual sub-tes.
- Halaman admin memantau timer siswa secara dinamis setiap 20 detik sekali.
- Reset session menendang siswa tetapi tidak merusak / mereset posisi mapel soal saat masuk kembali.
