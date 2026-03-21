# Analisis & Implementation Plan - Integrasi Editor Matematika (Strategi Hibrida)

Berikut adalah rencana eksekusi teknis untuk aplikasi Laravel Filament, dirancang agar guru mudah membuat soal dan siswa nyaman membacanya menggunakan kombinasi Tiptap dan perenderan Math.

## Fase 1: Perombakan Editor Inti (Sisi Admin)
**Fokus:** Membuang Trix yang kaku dan menggantinya dengan editor modern.
1.  **Langkah 1:** Uninstall atau nonaktifkan penggunaan `RichEditor` bawaan Filament.
2.  **Langkah 2:** Install package pihak ketiga `awcodes/filament-tiptap-editor` via Composer.
3.  **Langkah 3:** Implementasikan Tiptap Editor pada `BankSoalResource` (di field Soal dan Pembahasan) serta `BankStimulusResource`. Pastikan toolbar standar (Bold, Italic, Image, Table) berfungsi normal.

## Fase 2: Menangani Input Matematika (Sisi Guru)
**Fokus:** Memberikan jalan bagi guru untuk memasukkan persamaan matematika ke dalam editor Tiptap.
1.  **Langkah 1 (Standar):** Ajarkan/sepakati format penulisan LaTeX dasar. Misalnya, rumus harus diapit oleh tanda `$$` atau `\(`. Guru bisa menggunakan alat gratis luar (seperti codecogs atau mathlive.io) untuk men-generate teks LaTeX, lalu mem-paste teks tersebut ke Tiptap.
2.  **Langkah 2 (Advanced - Opsi MathLive):** Buat sebuah Custom Action (tombol) di toolbar Tiptap Filament atau sebuah Action di bawah field editor bernama "Insert Math". Saat diklik, muncul modal Filament berisi Virtual Keyboard MathLive. Setelah rumus selesai dibuat, script akan menyisipkan kode LaTeX murninya ke dalam teks Tiptap.

## Fase 3: Mesin Rendering (Sisi Siswa & Preview Admin)
**Fokus:** Mengubah teks database menjadi tampilan matematika yang cantik di layar.
1.  **Langkah 1:** Pilih **KaTeX** karena rendering-nya jauh lebih cepat di browser (sangat penting untuk aplikasi CBT agar loading ujian tidak berat).
2.  **Langkah 2:** Pasang file CSS dan JS KaTeX di file layout frontend utama Anda (halaman tempat siswa mengerjakan tryout).
3.  **Langkah 3:** Tambahkan script inisialisasi KaTeX agar otomatis memindai elemen HTML yang mengandung soal ujian dan merender semua teks yang berada di dalam tag khusus (misal `$$...$$`).
4.  **Langkah 4:** Pastikan rendering KaTeX ini juga dipasang di halaman "View" Filament agar admin bisa melakukan preview soal dengan benar.

## Fase 4: Standarisasi Database & Keamanan
**Fokus:** Memastikan integritas data.
1.  **Langkah 1:** Pastikan tipe kolom di database (MySQL/PostgreSQL) untuk Soal, Opsi Jawaban, dan Pembahasan adalah `TEXT` atau `LONGTEXT`.
2.  **Langkah 2:** Lakukan testing sanitasi. Pastikan Filament tidak melakukan stripping atau membuang karakter backslash (`\`) yang merupakan nyawa dari sintaks LaTeX saat proses simpan ke database.

---
> [!IMPORTANT]
> **Keputusan Integrasi UI:** Rencana ini mengadopsi pendekatan Hibrida. Input menggunakan Tiptap Editor untuk fleksibilitas (dengan opsi menyuntikkan LaTeX via Modal Custom Action), dan output dirender secara dinamis di klien menggunakan KaTeX demi performa. Keutuhan karakter backslash `\` saat transmisi data menjadi titik pengujian paling krusial di Fase 4.

## Progress Saat Ini (21 Maret 2026 - Final)
1. **Fase 1 (Tiptap Editor)**: Telah berhasil diimplementasikan pada `BankSoalResource` dan `BankStimulusResource`. Tinggi editor disesuaikan ke `200px` (`extraInputAttributes`) agar lebih lega.
2. **Fase 2 (MathLive)**: Custom Action "Insert Math" sudah terpasang. Isu Virtual Keyboard mati di modal akibat AJAX/Lazy load *fixed* dengan autoloader JS dinamis. Ditambahkan sinkronisasi Textarea alternatif berukuran besar untuk kenyamanan input manual.
3. **Fase 3 (KaTeX Rendering)**: KaTeX Auto-Render sudah terpasang pada tampilan Layar Siswa (`soal.blade.php`) dan Preview Admin (`lihat-soal.blade.php`).
4. **Fase 4 (Database)**: Struktur database telah divalidasi dan aman (`LONGTEXT`).

> [!NOTE]
> Seluruh kendala *Focus Trap* dan inisialisasi script di Modal Filament sudah terselesaikan. Komponen saat ini siap digunakan untuk input rumus matematika.
