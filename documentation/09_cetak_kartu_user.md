# 09. Fitur Cetak Kartu Peserta & Filter Sekolah

Fitur ini ditujukan agar Admin dapat mencetak Kartu Login peserta dengan mudah, baik untuk seluruh peserta maupun difilter per sekolah.

## 1. Spesifikasi Fitur
- **Tombol Cetak Kartu**: Tersedia langsung di Header halaman `Admin → User Peserta` (tombol merah, ikon printer 🖨️). Tidak perlu mencentang baris satu per satu.
- **Filter Sekolah untuk Cetak**: Saat tombol diklik, muncul dialog untuk memilih Sekolah (dropdown dinamis dari data di database) atau cetak Semua Sekolah sekaligus.
- **Filter Sekolah di Tabel**: Tombol Filter pada tabel User juga sudah dilengkapi filter Sekolah untuk memudahkan pencarian peserta per sekolah secara real-time.

## 2. Alur Cetak

1. Admin klik **Cetak Kartu** (merah) di header halaman User Peserta.
2. Pilih Sekolah yang ingin dicetak (bisa Semua atau spesifik).
3. Klik Submit → Browser membuka tab baru berisi halaman khusus kartu peserta.
4. Halaman kartu langsung menampilkan semua kartu dalam grid 3 kolom (per A4).
5. Dialog Print browser otomatis terbuka via `window.print()`.

## 3. Struktur File yang Dibuat/Dimodifikasi

### [NEW] `resources/views/print/kartu-peserta.blade.php`
- Template HTML murni (tanpa layout Filament) untuk render Kartu Peserta.
- Desain: Header abu gelap (gradient), Nama, Sekolah, kotak credential (Username + Password warna merah).
- CSS `@media print` mengatur agar 3 kartu per baris per lembar A4.
- JavaScript `window.print()` terpanggil otomatis setelah halaman dimuat.
- Header halaman menampilkan nama Sekolah yang difilter + tanggal cetak.

### [NEW] `app/Http/Controllers/KartuPesertaController.php`
- Menerima query parameter `sekolah` atau `ids` dari URL.
- Jika `sekolah=semua`, ambil semua peserta; jika nama sekolah tertentu, filter ke sekolah itu saja.
- Jika `ids`, filter berdasarkan ID yang dipilih (dari Bulk Action).
- Mengembalikan view `print.kartu-peserta` beserta label filter yang ditampilkan di header.

### [MODIFY] `routes/web.php`
- Ditambahkan Route `GET /print/kartu-peserta` dengan middleware `auth`.
- Mengarah ke `KartuPesertaController@print`.

### [MODIFY] `app/Filament/Resources/UserResource.php`
- **Filter Sekolah**: Menambahkan `SelectFilter::make('sekolah')` dengan opsi diambil dinamis dari database (distinct, sorted).
- **Bulk Action Cetak Kartu**: Tetap tersedia untuk kasus seleksi manual baris-baris tertentu.
- **Kolom Tryout**: Dihapus `->sortable()` karena alias subquery tidak bisa di-ORDER BY di MySQL.

### [MODIFY] `app/Filament/Resources/UserResource/Pages/ListUsers.php`
- Ditambahkan **Header Action "Cetak Kartu"** (tombol merah) dengan form pilih Sekolah.
- Setelah submit, redirect ke route `/print/kartu-peserta?sekolah=...`.
