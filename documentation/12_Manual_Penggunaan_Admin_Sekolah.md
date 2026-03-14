# Manual Penggunaan: Admin Sekolah (CBT Bimbel Excellent)

Dibuat: 14 Maret 2026  
Status: Aktif  

---

## 1. Pendahuluan
Sistem CBT sekarang menggunakan sistem **Admin Sekolah**. Setiap Admin yang dibuat akan dikunci (paten) ke satu sekolah tertentu. Admin tersebut bertanggung jawab untuk mengelola Kelas dan Peserta di bawah wewenang sekolahnya.

---

## 2. Fitur Utama Admin Sekolah

### A. Otoritas Wilayah (Sekolah Paten)
Admin hanya memiliki akses ke data di satu sekolah. Nama sekolah ini ditetapkan oleh **Super Admin** saat pembuatan akun Admin. Admin biasa tidak dapat mengubah data Sekolah (NPSN, Alamat, dll).

### B. Manajemen Kelas
- Admin dapat melihat daftar kelas di sekolahnya.
- Admin dapat menambah kelas baru.
- Saat menambah kelas, sekolah otomatis terkunci ke sekolah si Admin.
- **Jenjang** (SD/SMP/SMA/SMK) juga otomatis mengikuti profil Admin.

### C. Manajemen Peserta
- Admin dapat melihat, menambah, dan mengedit data peserta (siswa) di sekolahnya.
- **Auto-Filter Kelas**: Pilihan kelas saat menambah peserta otomatis disaring hanya untuk kelas yang ada di sekolah Admin.
- **Auto-Generate Nomor Peserta**: Nomor peserta dibuat otomatis dengan format: `[KODE_SEKOLAH]-[KODE_KELAS]-[NOMOR_URUT]`.

---

## 3. Langkah-Langkah Operasional (Quick Start)

### 1. Masuk ke Dasbor
Login dengan username/password yang diberikan oleh Super Admin. Anda akan langsung diarahkan ke dasbor dengan menu yang sudah disesuaikan dengan hak akses Anda.

### 2. Mengelola Kelas
- Masuk ke menu **Manajemen Peserta** > **Kelas**.
- Klik tombol **Buat** untuk menambah kelas.
- Masukkan **Nama Kelas** (contoh: 10-A).
- Klik **Simpan**.

### 3. Mengelola Peserta
- Masuk ke menu **Manajemen Peserta** > **User Peserta**.
- Klik tombol **Buat**.
- Isi **Nama Lengkap**.
- Pilih **Kelas** (hanya kelas sekolah Anda yang muncul).
- Username dan Password akan di-generate otomatis untuk kemudahan.
- Klik **Simpan**.

### 4. Cetak Kartu Peserta
- Di menu **User Peserta**, centang satu atau beberapa peserta.
- Klik tombol **Bulk Action** (di bawah atau di samping pencarian).
- Pilih **Cetak Kartu**.
- Sistem akan membuka tab baru dengan kartu yang siap diprint/simpan ke PDF.

---

## 4. Keamanan & Performa
- **Sticky Footer**: Tombol simpan selalu ada di layar bawah untuk memudahkan penggunaan di tablet.
- **Soft Deletes**: Data yang dihapus tidak langsung hilang permanen, bisa dipulihkan oleh Super Admin jika terjadi kesalahan.
- **Audit Log**: Setiap tindakan Admin (tambah/edit/hapus) dicatat dalam sistem untuk keperluan verifikasi.

---

*Dokumentasi ini dibuat untuk membantu Admin Sekolah menjalankan tugas operasional harian.*
