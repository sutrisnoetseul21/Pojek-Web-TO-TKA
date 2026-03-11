# 03. Workflow UX & Logika Aplikasi

## A. Workflow Admin (Input Soal)

### Langkah 1: Setup Paket Tryout
1.  Admin membuat **Paket Tryout** (nama + keterangan).
2.  Menambahkan **Mapel Items** (repeater):
    *   Pilih Mapel → jumlah soal, waktu mapel (menit), urutan.
    *   Pilih **Mode Soal**: ACAK atau MANUAL.
    *   Mode MANUAL: Pilih soal spesifik via checkbox.
    *   Mode ACAK: Soal diambil random dari bank berdasarkan `paket_id` + `mapel_id`.
3.  Cascading dropdown: Saat pilih paket di Bank Soal, mapel otomatis terfilter.

### Langkah 2: Input Soal ke Bank
1.  Admin masuk ke **Bank Soal → Create**.
2.  Pilih **Paket** dan **Mapel** (dropdown terfilter).
3.  Pilih **Tipe Soal** → form jawaban berubah dinamis:
    *   **PG Tunggal**: Radio button untuk kunci + skor per opsi.
    *   **PG Kompleks**: Checkbox (bisa pilih >1 benar) + skor per opsi.
    *   **Benar/Salah**: Pernyataan + dropdown Benar/Salah + skor per opsi.
4.  Opsional: Hubungkan ke **Stimulus/Wacana** yang sudah dibuat.
5.  Input pertanyaan (RichEditor), pembahasan, dan bobot.
6.  Simpan → redirect ke list.

### Langkah 3: Setup Jadwal
1.  Admin membuat **Jadwal Tryout** (pilih paket, tanggal, waktu, token).
2.  Token 6 karakter diberikan ke pengawas untuk dibagikan ke peserta.

---

## B. Workflow Siswa (Ujian)

### Alur Lengkap

```
Login → Biodata+Token → Konfirmasi → Soal (Mapel 1) → [Confirm] → Soal (Mapel 2) → ... → Selesai → Hasil
```

### Detail Alur Per-Mapel

```
┌──────────────────────────────────────────────┐
│ MAPEL 1: Bahasa Indonesia (30 menit)         │
│                                              │
│ Soal 1/4  →  Soal 2/4  →  Soal 3/4  →       │
│ Soal 4/4 [Lanjut Mapel Berikutnya ▸]         │
│                                              │
│ ┌──────────── KONFIRMASI (2 TAHAP) ────────┐ │
│ │ Tahap 1: ⚠️ Peringatan soal dikunci      │ │
│ │          Info soal belum dijawab          │ │
│ │          [Kembali] [Ya, Lanjutkan]        │ │
│ │                                          │ │
│ │ Tahap 2: 🔒 Apakah Anda Yakin?           │ │
│ │          Konfirmasi final                │ │
│ │          [← Kembali] [Ya, Saya Yakin!]   │ │
│ └──────────────────────────────────────────┘ │
│                                              │
│ ┌──────── TRANSITION SCREEN ──────────┐      │
│ │ 📚 Mapel berikutnya: Matematika     │      │
│ │ 3 soal • 25 menit                  │      │
│ │ [🚀 Mulai Mengerjakan]              │      │
│ └─────────────────────────────────────┘      │
└──────────────────────────────────────────────┘

┌──────────────────────────────────────────────┐
│ MAPEL 2: Matematika (25 menit)               │
│                                              │
│ Soal 1/3  →  Soal 2/3  →                    │
│ Soal 3/3 [✅ Selesai & Kumpulkan]            │
└──────────────────────────────────────────────┘
```

### Layout Halaman Soal

```
┌────────────────────────────────────────────────────────────────┐
│ Header: Soal X/Y  [A][B][C]  |  Timer 29:55  | TO 1 | Daftar │
├────────────────────────────────────────────────────────────────┤
│ Banner: BAHASA INDONESIA  Mapel 1 dari 3                      │
├────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────┐  ┌─────────────────────────────────┐ │
│  │  📖 WACANA/STIMULUS │  │  Instruksi tipe soal            │ │
│  │   (jika ada)        │  │  pertanyaan soal...             │ │
│  │   Teks + Gambar     │  │                                 │ │
│  │                     │  │  ○ A  teks jawaban A            │ │
│  │                     │  │  ● B  teks jawaban B  ← dipilih │ │
│  │                     │  │  ○ C  teks jawaban C            │ │
│  └─────────────────────┘  └─────────────────────────────────┘ │
├────────────────────────────────────────────────────────────────┤
│  [← Soal sebelumnya]    [🚩 Ragu-ragu]  [Soal berikutnya →]  │
│  (atau)                                 [Lanjut Mapel ▸]     │
│  (atau)                                 [✅ Selesai]          │
└────────────────────────────────────────────────────────────────┘
```

### Logika Navigasi
1.  **Dalam mapel**: Bebas navigasi prev/next dan jump via daftar soal.
2.  **Antar mapel**: Hanya maju (sequential), tidak bisa kembali ke mapel sebelumnya.
3.  **Timer**: Independen per mapel. Jika habis, auto-pindah ke mapel berikutnya.
4.  **Soal terakhir mapel terakhir**: Tombol "Selesai & Kumpulkan" menggantikan "Next".
5.  **Soal terakhir mapel non-terakhir**: Tombol "Lanjut Mapel Berikutnya" muncul.

### Tipe Soal & Rendering

| Tipe | Instruksi | Komponen UI |
|------|-----------|-------------|
| `PG_TUNGGAL` | "Pilih satu jawaban yang benar" | Radio buttons |
| `PG_KOMPLEKS` | "Pilih lebih dari satu jawaban" | Checkboxes |
| `BENAR_SALAH` | "Pilih lebih dari satu jawaban" | Checkboxes |

---

## C. Penyimpanan Jawaban

*   Jawaban di-save otomatis via **Fetch API** setiap kali dipilih (per soal).
*   Endpoint: `POST /tryout/jawab` dengan CSRF token.
*   Payload: `{ peserta_jadwal_id, bank_soal_id, jawaban, is_ragu }`.
*   Jawaban disimpan di `jawaban_peserta` tabel dengan cast `array`.
