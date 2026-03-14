# Status Proyek: Tryout TKA Bimbel Excellent

> **Terakhir diperbarui**: 14 Maret 2026

## 1. Pekerjaan yang Telah Selesai (Completed)

### A. Konsultasi & Arsitektur
*   [x] **Analisis Tech Stack**: Laravel 12 + FilamentPHP v3 + PostgreSQL (Prod) / MySQL (Local Docker).
*   [x] **Desain Database**: 10+ tabel (master, bank soal, transaksi).
*   [x] **Dokumentasi**: Folder `documentation/` diperbarui (13 file).

### B. Konfigurasi Sistem & Infrastruktur
*   [x] **Pindah Database**: Migrasi dari PostgreSQL ke MySQL (Docker).
*   [x] **Instalasi Ekstensi PHP**: `php-intl`, `php-gd`, `php-pgsql`.
*   [x] **Wake-on-LAN**: Script `setup_wol.sh`.

### C. Admin Panel (FilamentPHP)
*   [x] **Bank Soal & Stimulus**: Form dinamis, Repeater jawaban, Excel Import/Export wacana.
*   [x] **Paket Tryout**: Cascading dropdown, mode Acak/Manual.
*   [x] **User & Role Overhaul**:
    *   [x] Implementasi `super_admin` & `admin` berbasis Jenjang & Sekolah.
    *   [x] **Jenjang Consistency**: Locking jenjang Kelas berdasarkan Sekolah.
    *   [x] **Admin "Follow All Classes"**: Otomasi penugasan kelas bagi admin sekolah.
    *   [x] **Permission Split**: Pemetaan hak akses (Peserta, Bank Soal, TRYOUT).
    *   [x] **Jenjang Filtering**: Filter otomatis Mata Pelajaran berdasarkan jenjang admin.
    *   [x] **UX Optimization**: Redirect to index, Sticky footer, Sticky save buttons.
    *   [x] **Audit Log**: Integrasi Spatie Activitylog.

### D. Student Portal (Halaman Ujian)
... (tetap)

---

## 2. Bug Fixes Terbaru

| Bug | Solusi | Detail |
|-----|--------|------|
| SQL Driver Not Found | Install `php-pgsql` & `php-mysql` | Fix koneksi DB |
| Intl Extension Required | Install `php-intl` | Perbaikan formatting angka Filament |
| GD Extension Missing | Install `php-gd` | Syarat Laravel Excel |
| Typo Migration | `kategori_id` -> `kategori_ids` | Fix bug migration lama |
| Missing Admin Access | Tambah logic `role === admin` | Fix izin masuk Admin Panel |
| Role Scoping | Implementasi `getEloquentQuery` scoping | Fix keamanan data antar jenjang |
| Disabled Fields | Tambah `dehydrated()` pada Jenjang | Fix data tidak tersimpan saat form di-disable |
| Field Name Default | `name` made Nullable | Fix error saat create Admin/User |
| Filament Avatar | `getNameAttribute` accessor | Fix TypeError `getUserName` null |
| Unique Mapel | Composite Unique `kode` + `jenjang` | Fix duplikasi kode mapel antar jenjang |

---

## 3. Langkah Selanjutnya (Next Steps)

### Prioritas Tinggi
1.  **Excel Import Bank Soal**: Mengembangkan format Excel untuk butir soal (sedang direncanakan).
2.  **Sistem Penilaian**: Perhitungan total_nilai yang lebih presisi.
3.  **Timer Auto-submit**: Submit otomatis saat waktu habis.

### Prioritas Menengah
4.  **Math Editor**: Integrasi MathJax/KaTeX.
5.  **Randomization**: SHuffle urutan soal per siswa.
6.  **Export Hasil**: Export CSV/PDF hasil tryout.
