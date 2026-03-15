# Fitur Masa Upload Excel & Pembatasan Role Admin (Jenjang)

Dokumentasi ini mencakup penambahan fitur Import/Export Excel untuk beberapa modul pendukung Bank Soal serta penerapan regulasi hak akses `jenjang` bagi akun Admin.

## 1. Modul yang Tercover
Fitur ini diimplementasikan pada 4 Modul:
1. **Mata Pelajaran (`RefMapel`)**
2. **Kategori / Paket Soal (`RefPaketSoal`)**
3. **Stimulus / Wacana (`BankStimulus`)**
4. **Bank Soal (`BankSoal`)**

---

## 2. Regulasi Hak Akses (Role Restrictions)
Untuk akun dengan role **Admin** (yang memiliki ikatan variabel `jenjang` tertentu, misal SD/SMP/SMA), sistem menerapkan aturan ketat:
- **Filter Query**: Data yang ditampilkan pada tabel (List View) otomatis terfilter hanya yang memiliki `jenjang` sama dengan admin.
- **Form Validation**: Form Creation maupun Editor menolak penyimpanan jika pengguna mencoba menautkan data `Mata Pelajaran` dari jenjang yang berbeda.
- **Excel Template Reference**: Lembar referensi drop-down pada file Excel Template otomatis menyembunyikan opsi dari jenjang lain agar tidak terjadi insiden salah pilih.

---

## 3. Format Penamaan File Excel
Saat diunduh oleh Admin, nama file otomatis mendapat awalan nama sekolah dari admin tersebut:
- Format: `[Nama Sekolah] - Template [Nama Modul].xlsx`
- Jika diunduh oleh SuperAdmin (tanpa ikatan sekolah), otomatis diawali kata `Template`.

---

## 4. Mekanisme Import & Validasi

### A. Mata Pelajaran (`RefMapel`)
- **Headers**: `NAMA MAPEL`, `KODE MAPEL`, `JENJANG`
- **Aturan**: Column `jenjang` dilewati validasi constructor. Admin tidak diperkenankan mengimpor nilai `jenjang` selain yang telah dialokasikan pada profilnya.

### B. Kategori Soal (`RefPaketSoal`)
- **Headers**: `NAMA PAKET`, `MATA PELAJARAN`, `KETERANGAN`
- **Aturan**: Dropdown Mata Pelajaran menggunakan format `[ID] - [Nama] - [Jenjang]`. Sistem mengekstrak ID dan memastikan `jenjang` relevan.

### C. Stimulus (`BankStimulus`)
- **Headers**: `MAPEL (PILIH)`, `PAKET (PILIH)`, `JUDUL STIMULUS`, `TIPE (TEKS/GAMBAR/AUDIO)`, `KONTEN`
- **Aturan**: Dropdown `Paket` dan `Tipe` dibekali sensor `.setShowDropDown(true)` di spreadsheet agar Excel menampilkan panah selektor otomatis.

### D. Bank Soal (`BankSoal`)
- **Aturan**: Lembar reference data sheet hanya menayangkan `Stimulus` dan `Mapel` milik jenjang admin bersangkutan. Logika validasi impor melempar pengecualian (Exception) jika kode mapel baris baris tidak diizinkan.

---

## 5. Antarmuka (Filament Actions)
Fitur diletakkan pada halaman Index masing-masing Resource sebagai **Header Actions**:
1. **Download Template**: Menjalankan controller Export.
2. **Import Excel**: Membuka modal Upload, mengeksekusi Import class, ditutupi pesan push Notifikasi (`Notification`).
