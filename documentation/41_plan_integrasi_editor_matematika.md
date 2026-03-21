# Analisis & Implementation Plan - Integrasi Editor Matematika (MathLive) di Bank Soal

## 1. Analisis Masalah Saat Ini
Form pembuatan Bank Soal saat ini menggunakan `RichEditor` bawaan Filament v3 (yang berbasiskan Trix). Trix sangat terbatas kemampuannya dan secara bawaan tidak mendungkung penulisan persamaan matematika (math equations / rumus) maupun ekstensi plugin eksternal dengan mudah. Akibatnya, pembuat soal tidak bisa menuliskan rumus matematika kompleks secara langsung.

## 2. Alternatif Solusi

### Opsi A: Menggunakan Filament Tiptap Editor + Ekstensi KaTeX
Filament memiliki plugin populer bernama `awcodes/filament-tiptap-editor` yang menggantikan Trix dengan Tiptap. Tiptap mendukung ekstensi matematika berbasis KaTeX/LaTeX.
- **Kelebihan:** Terintegrasi rapi dengan ekosistem teks editor biasa.
- **Kekurangan:** Penulis soal harus bisa menulis syntax LaTeX secara manual. Tidak ramah pengguna untuk guru yang tidak terbiasa dengan coding (tidak ada keyboard visual).

### Opsi B: Membuat Komponen Kustom menggunakan MathLive (Rekomendasi)
Seperti yang Anda dengar, **MathLive** adalah open-source library yang menyediakan keyboard virtual langsung di layar (mirip keyboard smartphone, tapi khusus rumus matematika). 
- **Kelebihan:** Sangat interaktif. Guru bisa menekan tombol integral, pecahan, akar kuadrat, dll, dan MathLive akan mengubahnya menjadi syntax LaTeX di belakang layar.
- **Kekurangan:** Memerlukan pembuatan komponen Form Filament custom (membuat file View tipe blade dan Class Component yang meload *mathlive.js*).

## 3. Implementation Plan (Fokus pada Opsi B: MathLive)

### Fase 1: Pembuatan Custom Form Component `MathEditor`
1.  Membuat class component `App\Filament\Forms\Components\MathEditor` yang extends `Field`.
2.  Membuat view blade `resources/views/filament/forms/components/math-editor.blade.php`.
3.  Inject library MathLive secara asinkron atau melalu CDN ke dalam view tersebut menggunakan instruksi AlpineJS x-data. Mengaktifkan tag `<math-field>` yang disediakan MathLive.

### Fase 2: Implementasi pada Resource
#### [MODIFY] `BankSoalResource.php`
-   Ubah input soal yang sebelumnya hanya bergantung pada `RichEditor` biasa menjadi opsi gabungan, atau integrasi spesifik dimana teks diformat dan rumus di-trigger. Jika menggunakan murni `MathEditor`, kita tambahkan input khusus untuk mengetik rumus lalu mem-parsingnya saat ditampilkan.
-   *Catatan Opsional:* Kita juga bisa mencari package Filament third-party spesifik yang menggabungkan Tiptap dengan GUI MathLive jika tersedia, untuk menghindari pembuatan custom view manual (contoh, mencoba mencari package TinyMCE Filament yang sudah pre-load plugin math).

### Fase 3: Pengujian Display (Front-end & Tabel)
Bagian yang tak kalah penting adalah saat merender *Bank Soal* di layar frontend peserta ujian atau tabel Admin. Browser perlu merender LaTeX menjadi visual menggunakan library MathJax atau KaTeX yang dipanggil di global layout.

## User Review Required
> [!IMPORTANT]
> **Keputusan Integrasi UI:** Apakah Anda ingin input Matematika ini dibuat sebagai "kolom terpisah" di form (misal: "Teks Soal" dan "Rumus Soal"), **ATAU** Anda ingin kemampuan di mana di tengah kalimat teks deskripsi terdapat tombol khusus untuk menyuntikkan rumus matematika (Rich Text Editor terintegrasi)? Menggunakan opsi RichEditor Tiptap + Math biasanya lebih disukai secara format, tapi membuat custom keyboard sedikit lebih menantang dibandingkan field MathLive standalone.
