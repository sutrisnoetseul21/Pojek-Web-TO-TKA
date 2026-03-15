# Analisis Sistem Penilaian (Scoring) Pilihan Ganda Kompleks

Berdasarkan investigasi kode pada `StudentController::submit()`, berikut adalah hasil analisis sistem penilaian untuk tipe soal **Pilihan Ganda Kompleks (PG_KOMPLEKS)**.

---

## 🟢 1. Cara Kerja Saat Ini (Kode Sekarang)

Potongan kode perhitungan untuk `PG_KOMPLEKS` di `StudentController`:
```php
} elseif ($soal->tipe_soal === 'PG_KOMPLEKS') {
    // Multiple Answer: Sum skor dari opsi yang dipilih
    if (is_array($userJawaban)) {
        $skorDidapat = $soal->jawaban->whereIn('id', $userJawaban)->sum('skor');
        $totalNilai += $skorDidapat;
    }
}
```

**Temuan**:
Sistem hanya menjumlahkan (`sum`) kolom `skor` dari setiap opsi yang **dipilih** oleh siswa.

---

## 🔴 2. Analisis Kasus (Skenario User)

Skenario:
Terdapat 4 Opsi Jawaban:
-   Opsi A: **Benar** (Input Skor di Database = `1`)
-   Opsi B: **Benar** (Input Skor di Database = `1`)
-   Opsi C: **Benar** (Input Skor di Database = `1`)
-   Opsi D: **Salah** (Input Skor di Database = `0`)

### ⚡ Jika Siswa Menceklis SEMUA Opsi (A, B, C, D)
-   Pilihan Siswa: `[A, B, C, D]`
-   Perhitungan: `Skor A (1) + Skor B (1) + Skor C (1) + Skor D (0)` = **Total Poin = 3**

🚨 **MASALAH**:
Siswa mendapatkan **nilai sempurna** meskipun mereka memilih jawaban yang **salah**. Ini berarti siswa bisa "mengakali" sistem dengan menceklis semua kotak untuk mendapat nilai maksimal tanpa membaca soal.

---

## 🛠️ 3. Rekomendasi Solusi (Harusnya Bagaimana?)

Ada beberapa standar umum penilaian PG Kompleks. Anda bisa memilih salah satu untuk kami terapkan:

### 🌟 Opsi A: All or Nothing (Sangat Ketat)
Siswa mendapatkan nilai penuh **HANYA JIKA** kotak yang diceklis **100% tepat** sama dengan jawaban benar.
-   **Logika**:
    -   Jika siswa memilih `[A, B, C]` → Dapat **3** (Benar).
    -   Jika siswa memilih `[A, B, C, D]` → Dapat **0** (Ada salah satu dipilih).
    -   Jika siswa memilih `[A, B]` → Dapat **0** (Kurang pilihan).

---

### 🌟 Opsi B: Pengurangan Poin / Penalti (Sangat Adil)
Siswa mendapat skor untuk yang benar, tetapi dikurangi skor untuk setiap yang salah dipilih.
-   **Logika**:
    -   Jumlahkan skor opsi Benar yang dipilih.
    -   Kurangi skor untuk opsi Salah yang terpilih (misal penalti -1 setiap opsi salah).
-   **Hasil Skenario (Ceklis Semua)**:
    -   Opsi Benar (A+B+C) = 3
    -   Opsi Salah (D) = -1
    -   **Skor Akhir = 2** (Mencegah eksploitasi).

---

### 🌟 Opsi C: Proporsional Exact Match (Standar UTBK)
Bisa berupa model **Poin Proporsional** berbasis item.
Jika opsi yang salah dipilih, total item tersebut dinilai 0 untuk nomor itu secara penuh.

---

## 🚀 Langkah Selanjutnya
Silakan konfirmasi skema mana yang ingin diterapkan:
1.  **Opsi A (All or Nothing)**: Harus 100% sama persis baru dapat poin.
2.  **Opsi B (Penalti)**: Kurangi poin untuk setiap pilihan salah yang diceklis. Sub-pertanyaan: Berapa nilai pengurangan / penaltinya?

Saya siap memperbarui logic di `StudentController` berdasarkan preferensi Anda.
