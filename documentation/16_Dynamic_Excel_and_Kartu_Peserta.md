# Panduan Fitur: Dynamic Excel & Kartu Peserta

**Tanggal**: 15 Maret 2026
**Topik**: Manajemen Data Massal & Cetak Kartu Login

---

## 1. Sistem Upload Excel Dinamis

Sistem ini dirancang untuk memudahkan Admin maupun Super Admin dalam mengelola data peserta dan kelas dalam jumlah besar.

### A. Upload User Peserta
*   **Akses**: `Manajemen Peserta` -> `User Peserta` -> Tombol `Template Excel` & `Import Excel`.
*   **Cara Kerja**:
    *   **Super Admin**: Harus memilih sekolah terlebih dahulu sebelum download template. Template akan berisi daftar kelas yang tersedia hanya untuk sekolah tersebut.
    *   **Admin Sekolah**: Tidak perlu memilih sekolah. Template otomatis disesuaikan dengan sekolah tempat ia bertugas.
*   **Validasi**:
    *   Mengecek username duplikat.
    *   Memastikan kelas yang diinput di Excel benar-benar ada di sekolah tersebut.

### B. Upload Kelas
*   **Akses**: `Data Master` -> `Kelas` -> Tombol `Template Excel` & `Import Excel`.
*   **Cara Kerja**: Sama seperti User Peserta, template akan diberi nama sesuai sekolah yang dipilih (misal: `SMP Negeri 3 Kedungreja Template Kelas.xls`).
*   **Validasi**: Mencegah duplikasi nama kelas di sekolah yang sama.

---

## 2. Manajemen Kartu Peserta (Menu Baru)

Fitur cetak kartu kini dipisahkan ke menu khusus agar tidak mengganggu manajemen user utama.

### Fitur Utama:
1.  **Halaman Index**: Menampilkan daftar peserta dengan filter Sekolah dan Kelas yang cepat.
2.  **Cetak Masal**:
    *   Tombol di header yang memungkinkan cetak seluruh peserta dalam satu sekolah atau kelas tertentu.
    *   Membuka halaman **In-App Preview**.
3.  **In-App Preview**:
    *   Menampilkan simulasi layout kartu di atas kertas A4 sebelum dicetak.
    *   Dilengkapi tombol **Refresh** dan **Cetak** yang terintegrasi dengan browser print box.
4.  **Cetak Satuan & Terpilih**:
    *   Mendukung pencetakan baris satu-per-satu atau beberapa yang dipilih (Bulk Action).

### Cara Mencetak:
1.  Buka menu `Manajemen Peserta` -> `Kartu Peserta`.
2.  Gunakan filter untuk menemukan peserta.
3.  Klik `Cetak Masal` jika ingin mencetak per kelas/sekolah, atau centang peserta dan klik `Cetak Terpilih`.
4.  Pada halaman preview, pastikan data sudah benar, lalu klik tombol hijau `Cetak`.
5.  Pilih `Save as PDF` atau printer fisik pada dialog cetak browser.
