# Dokumentasi Overhaul: Role dan Otoritas User

> **Status**: Perencanaan (Planning)
> **Tanggal**: 13 Maret 2026

## 1. Latar Belakang
Sistem saat ini hanya memiliki role sederhana (`admin` dan `peserta`). Untuk skala yang lebih besar, diperlukan pembatasan akses data berdasarkan Jenjang (SD, SMP, SMA, SMK, Umum) dan Sekolah.

## 2. Struktur Role Baru
1.  **Super Admin**
    *   Otoritas tertinggi di seluruh sistem.
    *   Mengatur User Admin dan User Super Admin.
    *   Melihat seluruh data di semua jenjang dan sekolah.
2.  **User Admin**
    *   Otoritas terbatas pada satu **Jenjang** dan **Sekolah** tertentu.
    *   Hanya bisa mengelola Peserta di lingkupnya.
    *   Ditujukan untuk admin sekolah atau koordinator jenjang.
3.  **Peserta**
    *   Siswa yang mengerjakan ujian.

## 3. Rencana Teknis
*   **Database**:
    *   Update enum `role` di tabel `users` -> `super_admin`, `admin`, `peserta`.
    *   Tambah kolom `jenjang` di tabel `users` (sebagai filter otoritas).
    *   Update enum `jenjang` di tabel referensi untuk menyertakan `SMK`.
*   **UI (Filament)**:
    *   Pemisahan menu manajemen user menjadi 3:
        1.  `User Admin`
        2.  `User Super Admin`
        3.  `User Peserta`
*   **Scoping**:
    *   Implementasi filter otomatis pada query jika user yang login adalah `admin`.

## 4. Keamanan
*   User Admin tidak dapat melihat atau mengedit User Admin lain.
*   User Admin tidak dapat mengubah `jenjang` atau `sekolah` mereka sendiri.
