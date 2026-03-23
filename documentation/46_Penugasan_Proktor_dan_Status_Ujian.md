# Dokumentasi: Optimasi Fitur Penugasan Proktor dan Status Ujian

## Latar Belakang
Sebelumnya, fitur **Penugasan Proktor (Alokasi Pengawas)** memiliki beberapa kekurangan:
1. Validasi jadwal terlalu ketat (satu jadwal hanya boleh memiliki satu proktor secara keseluruhan).
2. Input kelas dibatasi 1 kelas per baris penugasan.
3. Status Ujian yang membingungkan antara status penugasan proktor dengan status akses token ujian untuk siswa.
4. Adanya kolom-kolom riwayat yang kurang relevan (Kelas Tugas dan Tanggal Dibuat) di tabel manajemen Proktor.

## Perubahan dan Peningkatan (Enhancements)

### 1. Perubahan Logika Validasi Proktor (Composite Unique)
Validasi _unique_ pada `jadwal_tryout_id` telah dihapus karena menghalangi pembagian ujian 1 jadwal ke berbagai proktor (untuk kelas/ruangan yang berbeda).
*   **Logika baru**: Cek duplikasi berbasis **kombinasi `jadwal_tryout_id` + `kelas_id`**.
*   **Hasil**: Sebuah jadwal dapat ditugaskan ke Proktor A (untuk kelas 7A) dan Proktor B (untuk kelas 7B) secara bersamaan.

### 2. Form Penugasan Massal (Repeater & Multi-select)
*   **Form Repeater**: Pembuatan penugasan (Create) sekarang menggunakan Custom Action bermodel form Repeater. Admin dapat memilih **1 Nama Proktor**, lalu menambahkan banyak baris penugasan jadwal dan ruangan sekaligus.
*   **Multi-select Kelas**: Di dalam tiap baris penugasan, "Kelas/Rombel" diubah menjadi _multiple select_. Jika dikosongkan, artinya "Semua Kelas" (menyimpan null). Jika dipilih beberapa, sistem akan membuatkan 1 baris record `JadwalRuanganProktor` untuk masing-masing kelas tersebut secara otomatis di belakang layar.

### 3. Pengelompokan Data Tabel Penugasan (Grouping)
Agar tampilan tidak penuh oleh puluhan baris kelas yang sama untuk 1 proktor, tabel `PenugasanProktorResource` sekarang dikelompokkan:
*   Query dimodifikasi dengan `WHERE IN (SELECT MIN(id) ... GROUP BY proktor_id, jadwal_tryout_id, ruangan_id)`.
*   Kolom **Kelas/Rombel** diubah agar dinamis (membaca seluruh kelas terkait pada grup tersebut) dan merendernya sebagai kumpulan Badge dalam 1 baris tabel.

### 4. Sinkronisasi UI Status Ujian
Status 'Aktif/Tidak Aktif' milik internal penugasan _(JadwalRuanganProktor)_ telah disembunyikan dari UI dan diubah default-nya selalu `active`. Sebagai gantinya, 2 kolom baru ditampilkan:
1.  **Jadwal Ujian**: Menampilkan status kronologis (Akan Datang, Berlangsung, Selesai) berdasarkan `tgl_mulai` dan `tgl_selesai`.
2.  **Status Ujian**: Menampilkan status toggle (Aktif / Tidak Aktif) yang terhubung langsung ke properti `is_active` milik `JadwalTryout`, sehingga tampilannya konsisten dengan menu Jadwal Tryout.

### 5. Pembersihan Tabel Proktor
*   Menghapus akun-akun *dummy* atau error referensi yang *nympang* di database langsung via tinker CLI.
*   Menghapus kolom "Kelas Tugas" dan "Dibuat Pada" dari tabel `ProktorResource` agar UI lebih bersih dan fokus pada informasi akun proktor (Username, Email, Nama).
