# 34. Halaman Status Peserta Monitoring Sesi

Halaman ini digunakan oleh proktor untuk memantau status pengerjaan ujian peserta secara statis/manual.

## 📌 Deskripsi Fitur
Menampilkan data status aktivitas peserta dari jadwal tryout aktif hari ini. Dilengkapi fungsionalitas berhenti paksa ("Selesai Tes") pada peserta yang sedang mengerjakan.

---

## 🛠️ Detail Implementasi

### 1. Optimalisasi Performa Timestamps & Relasi
Untuk mempercepat render dan memastikan akurasi logs aktivitas:
- **`app/Models/JawabanPeserta.php`**:   
  Menambahkan `protected $touches = ['pesertaJadwal'];` agar setiap kali peserta menyimpan jawaban auto-save, kolom `updated_at` di tabel induk (`peserta_jadwal`) ikut ter-update.
- **`app/Models/PesertaJadwal.php`**:  
  Menambahkan relasi `currentMapel()` ke tabel `ref_mapel` berdasarkan `current_mapel_id` untuk mempercepat pemanggilan nama subtes.

### 2. Layout & Filter halaman
- **Polling (`poll()`)**: Dinonaktifkan secara penuh untuk mengurangi beban kinerja server VPS.
- **Refresh Manual**: Menyertakan tombol **Refresh** panel di atas tabel agar proktor dapat me-reload data secara manual kapan pun dibutuhkan.
- **Status Badges**:
  - `Login` (Warning/Kuning)
  - `Sedang Dikerjakan` (Danger/Merah)
  - `Tes Selesai` (Success/Hijau)

---

## 🗑️ Cleanup Menu Legacy
Sebagai bagian dari standarisasi sistem Monitoring Ujian (Baru), rute/file lama berikut telah **dihapus**:
1. `BantuanPesertaResource`
2. `MonitoringSesi` Page
3. `PesertaJadwalResource`
4. `UjianActivityLogResource`

---

## 📂 File Terkait
- `app/Filament/Pages/StatusPeserta.php`
- `resources/views/filament/pages/status-peserta.blade.php`
- `app/Models/PesertaJadwal.php`
- `app/Models/JawabanPeserta.php`
