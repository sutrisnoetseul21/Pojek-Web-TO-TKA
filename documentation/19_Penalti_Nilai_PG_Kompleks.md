# Implementation Plan - Penalti Nilai Pilihan Ganda Kompleks (Pendekatan Database)

Tujuan: Menerapkan sistem pengurangan poin (penalti) untuk jawaban salah pada tipe soal Pilihan Ganda Kompleks (PG_KOMPLEKS) dengan menetapkan bobot penalti langsung di dalam database. Hal ini menyederhanakan logika controller dan mencegah eksploitasi "Ceklis Semua".

---

## 1. Persiapan Data (Database Level)

Sebelum masuk ke controller, pastikan skema penilaian pada tabel jawaban/opsi sudah dikonfigurasi dengan benar oleh pembuat soal:

*   **Opsi Benar**: Diberi nilai positif pada kolom skor (misal: 1, 2, 3, dst. tergantung bobot opsi).
*   **Opsi Salah (Pengecoh)**: Diberi nilai negatif pada kolom skor (misal: -1, -2, dst. sebagai nilai penalti).

> [!NOTE]
> Pastikan tipe data kolom skor di database mendukung bilangan negatif (seperti `INT` atau `TINYINT` signed, bukan unsigned).

---

## 2. Perubahan Kode (Controller Level)

### [Component] Perhitungan Nilai (Scoring)

#### [MODIFY] `StudentController.php`

Ubah logika di dalam fungsi `submit()` untuk tipe `PG_KOMPLEKS`. Karena logika penalti sudah ditangani oleh nilai minus di database, algoritma di controller menjadi sangat ringkas:

**Algoritma Baru**:
1.  Ambil koleksi data jawaban dari database berdasarkan array ID yang diceklis oleh user.
2.  Jumlahkan semua nilai skor dari jawaban yang dipilih (nilai negatif akan otomatis mengurangi total).
3.  Terapkan batas bawah (0) agar skor akhir satu nomor soal tidak pernah minus.

**Implementasi Kode (Laravel Eloquent)**:

```php
// Asumsi $userJawaban adalah array ID jawaban yang diceklis user, contoh: [12, 13, 15]

// 1 & 2. Ambil opsi yang dipilih dan langsung jumlahkan skornya
$skorKasar = $soal->jawaban->whereIn('id', $userJawaban)->sum('skor');

// 3. Batasi Batas Bawah (Mencegah nilai minus pada soal tersebut)
$skorAkhir = max(0, $skorKasar);

// $skorAkhir siap disimpan ke tabel riwayat/hasil ujian
```

---

## 3. Rencana Verifikasi (Simulasi Kasus)

Untuk memastikan logika ini berjalan sempurna, berikut simulasi dengan contoh data database.

**Kondisi Database untuk 1 Soal (Total Poin Maksimal = 3)**:
*   Opsi A (Benar) = skor: 1
*   Opsi B (Benar) = skor: 1
*   Opsi C (Benar) = skor: 1
*   Opsi D (Salah) = skor: -2

### Simulasi Pengujian:

1.  **Skenario A (Pilihan Tepat / Paham Materi)**
    *   User memilih: `{A, B, C}`
    *   Kalkulasi `sum('skor')`: `1 + 1 + 1 = 3`
    *   Filter `max(0, 3)`: **Skor Akhir = 3**

2.  **Skenario B (Eksploitasi Ceklis Semua)**
    *   User memilih: `{A, B, C, D}`
    *   Kalkulasi `sum('skor')`: `1 + 1 + 1 + (-2) = 1`
    *   Filter `max(0, 1)`: **Skor Akhir = 1** (*User rugi karena mencoba menebak semua*)

3.  **Skenario C (Pilih Sebagian & Terjebak)**
    *   User memilih: `{A, D}`
    *   Kalkulasi `sum('skor')`: `1 + (-2) = -1`
    *   Filter `max(0, -1)`: **Skor Akhir = 0**

4.  **Skenario D (Salah Total)**
    *   User memilih: `{D}`
    *   Kalkulasi `sum('skor')`: `-2`
    *   Filter `max(0, -2)`: **Skor Akhir = 0**

---

## Keuntungan Implementasi Ini:

*   **Performa Lebih Baik**: Query atau pemrosesan collection hanya dipanggil sekali. Tidak ada lagi looping atau perhitungan perkalian penalti yang rumit di sisi server.
*   **Fleksibilitas Bobot**: Guru atau pembuat soal memiliki kendali penuh. Mereka bisa membuat opsi pengecoh yang "sangat fatal" dengan nilai -3, dan opsi pengecoh "biasa" dengan nilai -1 murni dari antarmuka pembuat soal tanpa perlu menyentuh kode backend lagi.
