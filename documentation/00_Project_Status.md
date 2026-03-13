# Status Proyek: Tryout TKA Bimbel Excellent

> **Terakhir diperbarui**: 13 Maret 2026

## 1. Pekerjaan yang Telah Selesai (Completed)

### A. Konsultasi & Arsitektur
*   [x] **Analisis Tech Stack**: Laravel 12 + FilamentPHP v3 + PostgreSQL (Prod) / MySQL (Local Docker).
*   [x] **Desain Database**: 10+ tabel (master, bank soal, transaksi).
*   [x] **Dokumentasi**: Folder `documentation/` diperbarui.

### B. Konfigurasi Sistem & Infrastruktur
*   [x] **Pindah Database**: Migrasi dari PostgreSQL ke MySQL (Docker) untuk kelancaran pengembangan lokal.
*   [x] **Instalasi Ekstensi PHP**: Instalasi `php-intl`, `php-gd`, dan `php-pgsql` pada server lokal.
*   [x] **Wake-on-LAN**: Script `setup_wol.sh`.
*   [x] **Storage Link**: `php artisan storage:link` untuk upload gambar/media.

### C. Admin Panel (FilamentPHP)
*   [x] **Bank Soal Resource**:
    *   Form create/edit dengan tipe soal dinamis (PG_TUNGGAL, PG_KOMPLEKS, BENAR_SALAH).
    *   Repeater jawaban (reorderable, collapsible, cloneable) dengan skor per opsi.
*   [x] **Bank Stimulus Resource**:
    *   [x] **Fitur Excel Excel**: Tombol "Download Template" dengan dropdown otomatis untuk Mapel & Paket dari database.
    *   [x] **Fitur Import Excel**: Memproses file Excel wacana secara otomatis.
    *   RichEditor konten + file upload media.
*   [x] **Paket Tryout Resource**:
    *   Form dengan cascading dropdown.
    *   Mode soal: ACAK (random) / MANUAL.
*   [x] **User Management**:
    *   [x] **Security**: Penentuan role `admin` untuk akses panel Filament.
    *   [x] **Admin Seeding**: Penambahan user admin default (`admin@admin.com`).
    *   [ ] **Role Overhaul**: Perencanaan penambahan `super_admin` dan `admin` (jenjang & sekolah) sedang dilakukan.

### D. Student Portal (Halaman Ujian)
*   [x] **Halaman Soal**: Layout split screen, Timer per mapel, navigasi kontekstual.
*   [x] **Support Soal**: PG Tunggal, PG Kompleks, Benar/Salah (Tabel).
*   [x] **Automation**: Auto-save jawaban via AJAX.

---

## 2. Bug Fixes Terbaru

| Bug | Solusi | Detail |
|-----|--------|------|
| SQL Driver Not Found | Install `php-pgsql` & `php-mysql` | Fix koneksi DB |
| Intl Extension Required | Install `php-intl` | Perbaikan formatting angka Filament |
| GD Extension Missing | Install `php-gd` | Syarat Laravel Excel |
| Typo Migration | `kategori_id` -> `kategori_ids` | Fix bug migration lama |
| Missing Admin Access | Tambah logic `role === admin` | Fix izin masuk Admin Panel |

---

## 3. Langkah Selanjutnya (Next Steps)

### Prioritas Tinggi
1.  **Role & Otoritas User**: Perombakan sistem role ke `super_admin` dan `admin` berbasis jenjang (Direncanakan).
2.  **Excel Import Bank Soal**: Mengembangkan format Excel untuk butir soal (sedang direncanakan).
3.  **Sistem Penilaian**: Perhitungan total_nilai yang lebih presisi.
4.  **Timer Auto-submit**: Submit otomatis saat waktu habis.

### Prioritas Menengah
4.  **Math Editor**: Integrasi MathJax/KaTeX.
5.  **Randomization**: SHuffle urutan soal per siswa.
6.  **Export Hasil**: Export CSV/PDF hasil tryout.
