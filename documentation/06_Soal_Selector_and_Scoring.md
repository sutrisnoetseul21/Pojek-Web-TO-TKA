# 06. Soal Selector & Scoring Engine

## 1. Custom Soal Selector (Admin)

Untuk meningkatkan pengalaman pengguna dalam memilih soal secara manual di `PaketTryoutResource`, kami membuat komponen custom field.

### Lokasi File
-   **View**: `resources/views/filament/forms/components/soal-selector.blade.php`
-   **Resource**: `app/Filament/Resources/PaketTryoutResource.php`

### Fitur Utama
1.  **Grouping**: Soal dikelompokkan berdasarkan Paket Sumbernya.
2.  **Preview**: Setiap soal memiliki tombol "Lihat" yang membuka modal berisi detail stimulus, pertanyaan, dan opsi jawaban.
3.  **State Management**: Menggunakan kombinasi `Hidden` field (untuk data) dan `Placeholder` field (untuk UI) agar aman dari isu serialisasi Livewire.

### Cara Kerja
-   Field `Hidden` menyimpan array ID soal yang dipilih.
-   Field `Placeholder` merender view Blade yang berisi checkbox list dengan Alpine.js.
-   Data soal dikirim secara *lazy* melalui Closure untuk performa optimal.

## 2. Scoring Engine (Siswa)

Sistem penilaian menangani berbagai tipe soal dengan logika yang berbeda-beda.

### Tipe Soal & Logika Penilaian

| Tipe Soal | Format Jawaban (DB) | Logika Scoring |
| :--- | :--- | :--- |
| **PG Tunggal** | ID Opsi (String/Int) | Mencocokkan ID dengan kunci jawaban. Jika benar, ambil skor opsi tersebut. |
| **PG Kompleks** | Array ID Opsi `[1, 2, 5]` | Menjumlahkan skor dari setiap opsi yang dipilih. |
| **Benar/Salah** | JSON Object `{"id_opsi": "BENAR"}` | Iterasi setiap baris pernyataan. Jika jawaban user (BENAR/SALAH) cocok dengan kunci, tambahkan skor baris tersebut. |

### Penanganan Skor Parsial
Untuk tipe soal **Benar/Salah** dan **PG Kompleks**, sistem mendukung **Skor Parsial**. Artinya, jika siswa menjawab benar sebagian, mereka tetap mendapatkan poin untuk bagian yang benar tersebut, bukan 0 mutlak.

## 3. Perbaikan Terbaru (Bug Fixes)

### A. Tampilan Hasil Ujian (`hasil.blade.php`)
-   **Isu**: Jawaban Benar/Salah dianggap salah semua karena format data JSON tidak terbaca.
-   **Solusi**: Refactor tampilan hasil untuk mendeteksi tipe soal `BENAR_SALAH` dan melakukan iterasi pada JSON Object jawaban, bukan menganggapnya sebagai string tunggal.

### B. Mapel Selection (Admin)
-   **Isu**: Nama mata pelajaran sama (misal: "Matematika") muncul ganda tanpa pembeda jenjang.
-   **Solusi**: Modifikasi query opsi dropdown untuk menampilkan format `Nama Mapel - Jenjang` (contoh: "Matematika - SMA").
-   **Implementasi**: `RefMapel::all()->mapWithKeys(...)` pada semua Resource terkait (Bank Soal, Stimulus, Paket Soal).
