# Dokumentasi - Proteksi Login Ganda & Request Reset (ANBK Style)

Dokumen ini merangkum rentetan fitur pengetatan access control dan penanganan kendala *Concurrent Login* (Login Ganda) yang dipasang pada portal ujian siswa dan ruang kontrol Proktor.

---

## 🔒 1. Sistem Proteksi Login (Guard)
Dua lapis penyekat dipasang di `StudentController@login` sebelum menyambut login siswa:

1.  **Guard Aktifasi Proktor**: 
    Menolak siswa masuk jika statusnya di tabel `peserta_jadwal` masih `registered` (Non-Aktif). Menampilkan pesan:  
    `🔴 Username belum diaktifkan oleh Proktor.`
2.  **Guard Concurrent Login**:
    Mengecek apakah `user_id` yang bersangkutan sudah ada di tabel `sessions` MySQL. Jika ada, login diblokir dengan pesan:  
    `🔴 Username sedang digunakan oleh perangkat lain...`

---

## 🖥️ 2. Penambahan Interface Admin / Proktor

### A. Halaman `Daftar Login`
Menu ringan di panel `/admin/daftar-login` untuk menampilkan list siswa yang **Sedang Berlangsung** ujiannya:
*   **Kondisi**: Hanya menampilkan data jika siswa memiliki record aktif di tabel `sessions` & status mengerjakan.
*   **Aksi "Reset"**: Membebaskan kuncian satu/banyak siswa (Bulk) dengan langsung menghapus baris tabel `sessions`.

### B. Halaman `Request Reset Login` (Layanan Bantuan)
Menu antrean di panel `/admin/request-reset-login` mengadopsi mekanisme mandiri manual-approve:
1.  Siswa yang terhalang login ganda dapat mengklik tombol **Ajukan Request Reset**.
2.  Sistem menyalakan `request_reset_at = NOW()` di database **TANPA memutus sesi yang sedang berjalan** (Melindungi dari spam).
3.  Nama siswa masuk ke tabel antrean Proktor.
4.  Proktor menekan tombol **Setujui Reset** ➔ Sesi lama diputus, dan kunci terbuka.

---

## ⚙️ 3. Perubahan Pendukung (Database)
*   Menambahkan kolom `request_reset_at` (Timestamp, Nullable) ke tabel `peserta_jadwal`.
*   Mengaktifkan sync auto `current_mapel_id` demi mencegah pop-up *Sesi Mapel Berganti* ketika peserta berpindah materi ujian secara legal di browser lokal.

---

*Terpasang & Teruji Secara Fungsional pada 18 Maret 2026.*
