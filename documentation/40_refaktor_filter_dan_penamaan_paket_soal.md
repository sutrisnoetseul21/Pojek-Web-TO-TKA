# Dokumentasi Refaktor Filter & Penamaan Paket Soal

**Tanggal:** 21 Maret 2026

## Ringkasan Perubahan
Modul ini mencakup perbaikan tata letak (layout) filter serta penyeragaman terminologi dari "Kategori / Paket" menjadi "Paket Soal" di seluruh antarmuka administrator yang relevan dengan Bank Soal.

## 1. Perubahan Tata Letak (Layout) Filter
Sebelumnya, filter pada `BankSoalResource` ditumpuk secara vertikal yang menghabiskan ruang vertikal dan kurang rapi. 
Perubahan:
- **Penggunaan Form Grid:** Filter sekarang dibungkus di dalam `Grid::make(4)` (untuk Bank Soal dan Stimulus) dan `Grid::make(2)` (untuk Kategori Soal) menggunakan komponen `Filter::make('advanced_filters')`.
- **Horizontal Span:** Menambahkan `->columnSpanFull()` agar blok filter membentang penuh ke samping, sejajar rapi seperti pada menu Kelompok Tes.
- **Opsi "All":** Mengganti default "Select an option" menjadi "All" pada seluruh filter dropdown dependen dengan menambahkan `->placeholder('All')`.
- **Implementasi:** Diaplikasikan pada `BankSoalResource`, `BankStimulusResource`, dan `RefPaketSoalResource`.

## 2. Penyeragaman Terminologi (Rename Paket)
Untuk menghindari kebingungan antara istilah "Paket", "Kategori", dan "Folder", seluruh antarmuka kini diseragamkan menggunakan istilah **Paket Soal**.
- **Bank Soal:** Mengubah label form, kolom tabel, dan filter dari `Paket` menjadi `Paket Soal`.
- **Bank Stimulus:** Mengubah label terkait paket menjadi `Paket Soal`.
- **Referensi Paket Soal:** Mengganti secara menyeluruh label navigasi, label model, kolom tabel `nama_paket`, dan input form dari `Kategori Soal` menjadi `Paket Soal` dan `Nama Paket Soal`.
