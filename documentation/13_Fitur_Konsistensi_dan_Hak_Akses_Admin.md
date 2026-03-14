# Dokumentasi Fitur: Konsistensi Jenjang & Otoritas Admin

> **Tanggal**: 14 Maret 2026
> **Status**: Selesai (Completed)

## 1. Konsistensi Jenjang (Sekolah & Kelas)
Sistem sekarang memastikan integritas data antar level pendidikan secara otomatis.
- **Sekolah**: Super Admin wajib menentukan Jenjang (SD, SMP, SMA, SMK, UMUM) saat membuat atau mengedit data Sekolah.
- **Kelas**: Saat membuat Kelas, kolom Jenjang akan otomatis terisi mengikuti Jenjang dari Sekolah yang dipilih dan bersifat **terkunci (disabled)**. Hal ini mencegah kesalahan data di mana Sekolah SMP memiliki Kelas jenjang SMA.

## 2. Otoritas Admin: "Ikuti Semua Kelas"
Fitur baru pada manajemen User Admin untuk mempermudah pendelegasian tugas:
- **Mode Penugasan Kelas**:
    1. **Ikuti semua kelas di sekolah**: Admin secara otomatis akan memiliki akses ke seluruh kelas yang ada di sekolahnya, termasuk kelas baru yang akan dibuat di masa depan.
    2. **Pilih kelas tertentu**: Admin hanya memiliki akses ke kelas-kelas yang dipilih secara manual oleh Super Admin.
- **Otomasi**: Jika mode "Ikuti semua" aktif, setiap kali ada Kelas baru dibuat di sekolah tersebut, Admin tersebut akan otomatis terdaftar sebagai pengelola kelas tersebut tanpa intervensi manual.

## 3. Penyempurnaan Hak Akses (Permissions)
Hak akses Admin telah dipecah menjadi 3 kategori utama agar lebih spesifik:
1. **👥 Kelola Kelas dan Peserta**: Mengelola data Siswa (Peserta), Sekolah, dan Kelas.
2. **📝 Kelola Bank Soal**: Mengelola Mata Pelajaran (Mapel), Paket Soal, Bank Soal, dan Stimulus.
3. **🏆 Kelola TRYOUT**: Mengelola Paket Tryout dan Jadwal Tryout.

## 4. Filtering Data Berdasarkan Jenjang
Untuk menjaga kerapihan kerja Admin:
- Saat Admin (misal: SMP) membuat Kategori Soal, Bank Soal, Stimulus, atau Paket Tryout, pilihan **Mata Pelajaran** otomatis hanya menampilkan mapel yang sesuai dengan Jenjang Admin tersebut (SMP).
- Hal ini mencegah tercampurnya data antar jenjang (misal: Admin SMP tidak sengaja memilih Mapel SMA).

## 5. Perbaikan Teknis
- **Fix Permission Save**: Memperbaiki bug di mana pemilihan banyak hak akses tidak tersimpan dengan benar (sekarang menggunakan Filament Relationship sync).
- **Table badges**: Menampilkan badge "Semua" pada kolom jumlah kelas untuk admin yang menggunakan mode otomatis.
