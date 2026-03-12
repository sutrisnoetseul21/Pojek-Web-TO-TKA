# Fitur Upload / Import & Export Bank Soal Excel

Fitur ini dirancang untuk memudahkan Admin/Guru memasukkan data Soal ke dalam Bank Soal secara massal menggunakan template Excel, lengkap dengan dukungan Stimulus (Wacana) dan Opsi Jawaban Dinamis.

## 1. Spesifikasi Fitur
- **Target Model**: `BankSoal` dan `BankJawaban`
- **Tipe Soal yang Didukung**:
    1. Pilihan Ganda Tunggal (`PG_TUNGGAL`)
    2. Pilihan Ganda Kompleks (`PG_KOMPLEKS`)
    3. Benar / Salah (`BENAR_SALAH`)
- **Fitur Khusus**:
    - Relasi opsional ke `BankStimulus`.
    - Dukungan hingga 10 Opsi Jawaban (sangat berguna untuk tipe Benar/Salah yang berderet, di mana satu soal bisa memiliki lebih dari 5 pasang pernyataan Benar/Salah).

## 2. Struktur Template Excel (`BankSoalTemplateExport`)
Template akan diekspor dalam bentuk 2 Sheet.

### Sheet 1: Form Soal (Tempat Input)
- **Kolom A**: MAPEL (Wajib, Dropdown dari referensi data)
- **Kolom B**: KATEGORI / PAKET (Wajib, Dropdown dari referensi data)
- **Kolom C**: STIMULUS (Opsional, Dropdown dari referensi data dengan format `ID - Judul`)
- **Kolom D**: TIPE SOAL (Wajib, Dropdown dari referensi data: `PG_TUNGGAL`, `PG_KOMPLEKS`, `BENAR_SALAH`)
- **Kolom E**: PERTANYAAN (Teks soal/pertanyaan utama)
- **Kolom F**: PEMBAHASAN (Opsional, penjelasan jawaban)
- **Kolom G**: BOBOT SOAL (Wajib, angka)
- **Kolom H s/d Kolom AA**: OPSI 1 hingga OPSI 10.
  - Setiap Opsi memiliki 2 sub-kolom: **Teks Opsi** dan **Kunci/Skor**.
  - Terdapat baris contoh dummy di file ekspor pertama untuk memandu pengguna.

### Sheet 2: Referensi_Data (Hidden/Protect Recommended)
Berguna untuk referensi Dropdown / Data Validation pada Sheet "Form Soal", nama disatukan tanpa spasi agar formula DataValidation di Excel stabil.
- Daftar Mapel (Format: `ID - Nama Mapel - Jenjang`)
- Daftar Kategori/Paket (Format: `ID - Nama Paket`)
- Daftar Stimulus (Format: `ID - Judul Stimulus`)
- Tipe Soal (Daftar tipe soal statis yang didukung)

## 3. Logika Import Excel (`BankSoalImport`)
Proses import dilakukan dengan Maatwebsite Excel (`ToModel` / `ToCollection`).

### A. Ekstraksi Relasi
- Data Mapel, Paket, dan Stimulus dikirim dari Dropdown dalam format string `ID - Nama`.
- Sistem menggunakan fungsi String manipulasi (mis. `Str::before($value, ' -')`) untuk mendapatkan Integer ID yang akan disimpan ke database.
- Jika kolom Stimulus kosong, field `stimulus_id` diset ke `null` (artinya soal berdiri sendiri tanpa wacana).

### B. Penyimpanan Bank Soal
- Record `BankSoal` baru dibuat menggunakan ID relasi yang telah diekstrak, beserta data Tipe, Pertanyaan, Pembahasan, dan Bobot.

### C. Pemrosesan Opsi Jawaban (Dinamis)
- Loop dilakukan dari Opsi 1 hingga Opsi 10.
- Jika kolom Teks Opsi kosong, loop untuk opsi tersebut di-skip (tidak dibuatkan record `BankJawaban`).
- Jika kolom Teks Opsi terisi, record `BankJawaban` dibuat dan dihubungkan ke `soal_id` yang baru saja dibuat.
- **Logika Penilaian (Scoring & Kunci):**
    - Untuk tipe `PG_TUNGGAL` & `PG_KOMPLEKS`: Kolom Kunci/Skor dibaca sebagai angka (Integer). Disimpan ke field `skor`. Field `kunci_jawaban` diset `null`.
    - Untuk tipe `BENAR_SALAH`: Kolom Kunci/Skor dibaca sebagai string literal (`BENAR` atau `SALAH`). String ini disimpan ke field `kunci_jawaban`. Skrip backend secara otomatis akan memberikan `skor = 1` jika kuncinya "BENAR", dan `skor = 0` jika kuncinya "SALAH" (atau sesuai rule yang ditetapkan di logic controller).

## 4. Integrasi User Interface (Filament)
- Endpoint/Action berada di `ListBankSoals.php` (halaman index Bank Soal).
- Ditambahkan Header Action:
  1. `Download Template`: Mengeksekusi export Excel.
  2. `Import Excel`: Membuka modal form File Upload, lalu mengeksekusi class Import.
