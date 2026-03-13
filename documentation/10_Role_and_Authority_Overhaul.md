# Dokumentasi Overhaul: Role dan Otoritas User

> **Status**: Selesai (Completed)
> **Tanggal**: 13 Maret 2026

## 1. Latar Belakang
Sistem telah diperbarui untuk mendukung pembatasan akses data berdasarkan Jenjang (SD, SMP, SMA, SMK, Umum) dan Sekolah/NPSN untuk meningkatkan keamanan dan kerapihan data.

## 2. Struktur Role
1.  **Super Admin**
    *   Otoritas tertinggi. Mengatur User Admin dan Super Admin.
2.  **User Admin**
    *   Otoritas terbatas pada satu **Jenjang**, **Sekolah**, dan **NPSN** tertentu.
    *   Hanya bisa mengelola Peserta di lingkupnya.
3.  **Peserta**
    *   User yang mengikuti ujian.

## 3. Fitur Utama & Keamanan
*   **Paten Sekolah & NPSN**: Data jenjang, sekolah, dan NPSN bagi Admin bersifat wajib dan mengikat ("paten").
*   **Auto-Inheritance**: Peserta yang dibuat oleh seorang Admin otomatis mewarisi Jenjang, Sekolah, dan NPSN Admin tersebut.
*   **Field Protection**: Admin tidak dapat mengubah Jenjang, Sekolah, atau NPSN mereka sendiri melalui profil.
*   **UX Redirect**: Setelah Create atau Edit user, sistem otomatis kembali ke halaman daftar (Index) untuk efisiensi kerja.

## 4. Teknis
*   **Database**: Kolom `name` pada tabel `users` dibuat nullable dengan fallback ke `username` untuk identitas Filament.
*   **Scoping**: Penggunaan `getEloquentQuery` di Filament Resources untuk membatasi visibilitas data secara otomatis.
*   **Unique Constraints**: Kode Mapel bersifat unik per Jenjang (Composite Unique).
