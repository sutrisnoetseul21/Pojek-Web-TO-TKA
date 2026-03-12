# Fitur Upload / Import User Excel & Konfigurasi Prefix

Fitur ini dirancang untuk memudahkan Admin/Guru memasukkan data User (khususnya Peserta) ke dalam sistem secara massal menggunakan file Excel, sekaligus menyempurnakan fitur "Generate Batch Users" agar pembentukan username lebih fleksibel dan sesuai permintaan.

## 1. Spesifikasi Fitur
- **Pengaturan Awalan Username (Prefix)**: Fasilitas untuk menentukan awalan username (misal: `P202600`) yang dapat diubah kapan saja. Nilai ini akan disimpan dan digunakan baik saat *Generate Batch* maupun *Import Excel*.
- **Logika Penomoran Otomatis**: Penomoran username *tidak lagi* menggunakan padding nol mati (seperti 001, 002). Angka akan langsung diurutkan dan ditempelkan di belakang prefix (contoh: Prefix `P202600` + urutan `1` menjadi `P2026001`, urutan `100` menjadi `P202600100`).
- **Import User dari Excel**: 
    - Mendukung format Excel sederhana untuk menginput Biodata. 
    - Username dan Password akan **di-generate secara otomatis** oleh sistem mengikuti perhitungan prefix di atas jika tidak diisi secara manual di Excel.

## 2. Struktur Template Excel (`UserTemplateExport.php`)
Template Excel yang disediakan akan memuat kolom-kolom berikut:
- **NAMA LENGKAP** (Wajib): Nama peserta.
- **SEKOLAH** (Opsional): Nama sekolah peserta.
- **JENIS KELAMIN** (Opsional): Pilih 'L' atau 'P'.
- **USERNAME** (Opsional): Jika dikosongkan, sistem akan otomatis membuatkannya berdasarkan *Prefix* terakhir.
- **PASSWORD** (Opsional): Jika dikosongkan, sistem akan mambuatkan password acak (5 huruf kapital + tanda bintang `*`).

## 3. Integrasi User Interface (Filament)
Pada halaman `ListUsers.php` (Menu Admin -> User Peserta), akan ada penyesuaian Header Action:
1. **[BARU] Pengaturan Username**: Sebuah form sederhana untuk mengatur dan menyimpan `Awalan Username (Prefix)`. Data ini disimpan di dalam *Laravel Cache* sehingga persisten.
2. **[MODIFIKASI] Generate Batch Users**: Input "Tahun Prefix" akan diubah menjadi mengambil nilai Default dari *Pengaturan Username* tadi, beserta penyesuaian logika penomoran agar sesuai urutan (tanpa padding fix 3 digit).
3. **[BARU] Download Template User**: Untuk mendownload format file Excel kosong.
4. **[BARU] Import Excel User**: Modal untuk mengunggah file Excel, memicu proses import.

## 4. Logika Import Excel (`UserImport.php`)
- Sistem membaca baris Excel.
- Menyiapkan variabel pembantu untuk mengambil angka terakhir dari database berdasarkan *Prefix* yang sedang aktif.
- Jika baris Excel pada kolom `USERNAME` kosong:
    - Increment angka terakhir + 1.
    - Username = `Prefix` + `Angka Baru` (misal P202600 + 1 = P2026001).
- Jika baris Excel pada kolom `PASSWORD` kosong:
    - Panggil fungsi `UserResource::generatePassword()`.
- Buat record akun `User` dengan Role `peserta` dan simpan informasi profil bawaannya `is_biodata_complete = false`.
