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
*   [x] **Paket Tryout**: 
    *   [x] Cascading dropdown, mode Acak/Manual.
    *   [x] **School Association**: Paket kini bisa dikunci ke sekolah tertentu.
*   [x] **Jadwal Tryout**:
    *   [x] **Target Sekolah & Kelas**: Penjadwalan kini membidik sekolah dan kelas spesifik.
    *   [x] **Auto-Assignment**: Siswa otomatis terdaftar ke jadwal berdasarkan kelas yang dipilih.
*   [x] **User & Role Overhaul**:
    *   [x] Implementasi `super_admin` & `admin` berbasis Jenjang & Sekolah.
    *   [x] **Jenjang Consistency**: Locking jenjang Kelas berdasarkan Sekolah.
    *   [x] **Admin "Follow All Classes"**: Otomasi penugasan kelas bagi admin sekolah.
    *   [x] **Permission Split**: Pemetaan hak akses (Peserta, Bank Soal, TRYOUT).
    *   [x] **Jenjang Filtering**: Filter otomatis Mata Pelajaran berdasarkan jenjang admin.
    *   [x] **UX Optimization**: Redirect to index, Sticky footer, Sticky save buttons.
    *   [x] **Audit Log**: Integrasi Spatie Activitylog.
*   [x] **Monitoring Ujian (4 Menu)**:
    *   [x] **Peserta Sedang Tes**: Monitoring real-time dengan polling & force submit.
    *   [x] **Monitoring Sesi**: Ringkasan statistik sesi.
    *   [x] **Log Aktivitas**: Record detail aktivitas per peserta.
    *   [x] **Bantuan Peserta**: Reset sesi, izin login, tambah waktu.
*   [x] **Laporan / Hasil (2 Menu)**:
    *   [x] **Hasil Sementara**: Pantau progres jawaban & nilai real-time.
    *   [x] **Hasil Tryout**: Laporan akhir lengkap + Ranking.

### D. Student Portal (Halaman Ujian)
*   [x] **UI Personalization**: Nama lengkap kini ditampilkan di semua header (Ujian, Biodata, Hasil) menggantikan username.
*   [x] **Robust Scoring Logic**:
    *   [x] Perbaikan skor **Benar-Salah** (mendukung identifikasi "Salah" dengan poin default 1).
    *   [x] Sinkronisasi logika skor antara Backend (Controller) dan Frontend (Blade).
    *   [x] Penanganan fallback jika kunci jawaban di database bernilai null.
*   [x] **Persistence**: Reset sesi & tambah waktu via Admin Panel.

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
| BS Scoring Fix | Default `skor` = 1 for "SALAH" | Fix poin 0 pada identifikasi Salah di BS |
| Hasil Tryout Crash | `nilai_akhir` -> `total_nilai` | Fix SQL error di Laporan Hasil Tryout |
| Missing Relationship | added `jawabanPeserta` in Model | Fix crash pada Hasil Sementara |

---

## 3. Langkah Selanjutnya (Next Steps)

### Prioritas Tinggi
1.  **Excel Import Bank Soal**: Mengembangkan format Excel untuk butir soal (sedang direncanakan).
2.  **Sistem Penilaian**: Perhitungan total_nilai yang lebih presisi.
3.  **Timer Auto-submit**: Submit otomatis saat waktu habis.

### Prioritas Menengah
4.  **Math Editor**: Integrasi MathJax/KaTeX.
5.  **Randomization**: Shuffle urutan soal per siswa.
6.  **Export Hasil**: Export CSV/PDF hasil tryout.
7.  **Sistem Penilaian**: Perhitungan total_nilai yang lebih presisi (Selesai).
8.  **Timer Auto-submit**: Submit otomatis saat waktu habis (Selesai).
