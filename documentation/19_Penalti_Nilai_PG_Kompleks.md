# Implementation Plan - Penalti Nilai Pilihan Ganda Kompleks

Menerapkan sistem pengurangan poin (penalti) untuk jawaban **salah** yang diceklis pada tipe soal **Pilihan Ganda Kompleks (PG_KOMPLEKS)** guna mencegah eksploitasi "Ceklis Semua".

---

## 1. Perubahan Kode

### [Component] Perhitungan Nilai (Scoring)

#### [MODIFY] [StudentController.php](file:///home/share-folder/data-ubuntu-24/Documents/Projek-web-Tryout-TKA/TRYOUT-TKA-Bimbel-Excellent/app/Http/Controllers/StudentController.php)
Ubah logika di dalam fungsi `submit()` untuk tipe `PG_KOMPLEKS`:

**Algoritma Baru**:
1.  Ambil semua opsi yang dipilih user (`$userJawaban`).
2.  Hitung **Skor Positif** dari opsi Benar yang dipilih:
    -   `$correctChoices = $soal->jawaban->whereIn('id', $userJawaban)->where('skor', '>', 0)`
    -   `$skorDidapat = $correctChoices->sum('skor')`
3.  Hitung **Jumlah Opsi Salah** yang dipilih:
    -   `$wrongChoices = $soal->jawaban->whereIn('id', $userJawaban)->where('skor', '<=', 0)`
4.  Hitung **Besaran Penalti**:
    -   Gunakan nilai skor dari salah satu opsi benar sebagai pengali penalty (agar adil dan proporsional terhadap bobot).
    -   `$penaltyAmount = $soal->jawaban->where('skor', '>', 0)->first()->skor ?? 1`
5.  Kurangi Skor Didapat:
    -   `$skorDidapat -= $wrongChoices->count() * $penaltyAmount`
6.  Batasi Batas Bawah:
    -   `$skorDidapat = max(0, $skorDidapat)` (Skor nomor tersebut tidak boleh minus).

---

## 2. Rencana Verifikasi

### Verifikasi Manual (Simulasi Kode)
1.  **Skenario A (Pilihan Tepat)**: 
    -   User memilih `{A, B, C}` (Benar semua).
    -   Hasil: `+3` poin.
2.  **Skenario B (Ceklis Semua)**:
    -   User memilih `{A, B, C, D}` (Ada 1 salah).
    -   Perhitungan: `3` (Benar) - `1 * 2` (Penalti) = `1` poin.
3.  **Skenario C (Salah Semua)**:
    -   User memilih `{D}` (Hanya salah).
    -   Perhitungan: `0` - `1 * 2` = `-2` → Dibulatkan ke **0** poin.
