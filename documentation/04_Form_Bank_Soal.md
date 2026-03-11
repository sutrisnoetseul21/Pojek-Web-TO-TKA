# 04. Dokumentasi Form Bank Soal & Admin Panel

## A. Bank Soal Resource (`BankSoalResource.php`)

### Form Input Soal

#### Bagian 1: Identitas & Pengaturan

| Field | Tipe | Keterangan |
|-------|------|------------|
| **Paket** | Select (searchable) | Relasi ke `paket_tryout` |
| **Mata Pelajaran** | Select (searchable) | Relasi ke `ref_mapel`, terfilter berdasarkan paket (cascading) |
| **Tipe Soal** | Select | `PG_TUNGGAL`, `PG_KOMPLEKS`, `BENAR_SALAH`. Helper text dinamis |
| **Stimulus** | Select (searchable) | Relasi ke `bank_stimulus`. Nullable (kosong = soal mandiri) |
| **Bobot Nilai** | Number | Skor default soal |

#### Bagian 2: Konten Pertanyaan
*   **RichEditor** untuk `pertanyaan`:
    *   Format teks (Bold, Italic, Heading)
    *   Upload gambar ke `storage/app/public/soal-images/`
    *   Link
*   **Textarea** untuk `pembahasan` (opsional)

#### Bagian 3: Opsi Jawaban (Repeater)

Fitur repeater: **Reorderable**, **Collapsible**, **Cloneable**.

| Field | Tipe | Keterangan |
|-------|------|------------|
| Teks Jawaban | TextInput | Teks pilihan jawaban |
| Skor | Number | Poin +/- jika dipilih |
| Kunci Jawaban | Toggle/Select* | *Berubah berdasarkan tipe soal |

*Kunci Jawaban berubah berdasarkan tipe soal:
*   **PG Tunggal**: Toggle (Benar?)
*   **PG Kompleks**: Toggle (Benar?)
*   **Benar/Salah**: Select (Benar/Salah)

### Tabel List Bank Soal

| Kolom | Source | Keterangan |
|-------|--------|------------|
| Paket | `paket.nama_paket` | Nama paket |
| Mapel | `mapel.nama_mapel` | Nama mata pelajaran |
| Soal | `pertanyaan` | Preview 50 karakter |
| Tipe | `tipe_soal` | Badge berwarna |
| Bobot | `bobot` | Numerik |

**Actions**: View, Edit, Delete
**Filter**: Mapel, Paket, Tipe Soal

---

## B. Bank Stimulus Resource (`BankStimulusResource.php`)

### Form Input Stimulus

| Field | Tipe | Keterangan |
|-------|------|------------|
| Judul | TextInput | Judul wacana |
| Mapel | Select | Relasi ke `ref_mapel` |
| Paket | Select | Relasi ke `paket_tryout` (terfilter berdasarkan mapel) |
| Tipe | Select | TEKS, AUDIO, VIDEO, GAMBAR |
| Konten | RichEditor | Isi wacana (HTML) + upload gambar |
| Media | FileUpload | Upload file audio/video/gambar ke `stimulus-media/` |

### Konfigurasi Upload
```php
RichEditor::make('konten')
    ->fileAttachmentsDisk('public')
    ->fileAttachmentsDirectory('stimulus-images')
    ->fileAttachmentsVisibility('public')
```

---

## C. Paket Tryout Resource (`PaketTryoutResource.php`)

### Form Paket Tryout

| Field | Tipe | Keterangan |
|-------|------|------------|
| Nama Paket | TextInput | "Tryout Akbar 2026" |
| Keterangan | Textarea | Deskripsi paket |

#### Mapel Items (Repeater)

Setiap item mapel dalam paket:

| Field | Tipe | Keterangan |
|-------|------|------------|
| Mapel | Select | Pilih mata pelajaran |
| Jumlah Soal | Number | Jumlah soal untuk mapel ini |
| Waktu Mapel | Number | Durasi dalam menit |
| Mode Soal | Select | `ACAK` atau `MANUAL` |
| Soal (Manual) | CheckboxList | Muncul jika mode MANUAL, pilih soal spesifik |
| Urutan | Number | Urutan tampil |

**Fitur Khusus:**
*   **Total soal** ditampilkan berdasarkan jumlah di bank_soal (bukan input manual).
*   **"Lihat Soal"** modal untuk preview soal yang akan masuk ke paket.
*   **Cascading dropdown**: Pilih paket → mapel terisi otomatis dari relasi.

---

## D. Jadwal Tryout Resource

| Field | Tipe | Keterangan |
|-------|------|------------|
| Paket Tryout | Select | Pilih paket |
| Nama Jadwal | TextInput | "Sesi 1 - Pagi" |
| Tanggal | DatePicker | |
| Waktu Mulai/Selesai | TimePicker | |
| Token | TextInput | 6 karakter, auto-generate |
| Status | Select | active / inactive |

---

## E. File Terkait

```
app/
├── Filament/Resources/
│   ├── BankSoalResource.php           # Form + Table soal
│   ├── BankSoalResource/Pages/
│   │   ├── CreateBankSoal.php
│   │   ├── EditBankSoal.php
│   │   └── ListBankSoals.php
│   ├── BankStimulusResource.php       # Form + Table stimulus
│   ├── PaketTryoutResource.php        # Form + Table paket
│   ├── JadwalTryoutResource.php       # Form + Table jadwal
│   └── UserResource.php               # Manajemen user
├── Models/
│   ├── BankSoal.php                   # relasi: jawaban(), stimulus(), mapel(), paket()
│   ├── BankJawaban.php                # fillable: teks_jawaban, skor, kunci_jawaban, label
│   ├── BankStimulus.php               # fillable: judul, konten, tipe, media_path
│   ├── PaketTryout.php                # relasi: mapelItems()
│   ├── PaketTryoutMapel.php           # method: getSoal() — logic ACAK/MANUAL
│   ├── JadwalTryout.php               # relasi: paketTryout()
│   ├── PesertaJadwal.php              # relasi: user(), jadwalTryout()
│   └── JawabanPeserta.php             # cast: jawaban=>array, is_ragu=>boolean
```

---

## F. Catatan Pengembangan

### Skor Per Opsi (Bukan Per Soal)
Setiap opsi jawaban memiliki `skor` tersendiri:
*   Jawaban benar: skor positif (misal +4)
*   Jawaban salah: skor 0 atau negatif (misal -1)
*   Tidak dijawab: skor 0

### Mode ACAK vs MANUAL di `PaketTryoutMapel::getSoal()`
```php
public function getSoal()
{
    if ($this->mode_soal === 'MANUAL' && !empty($this->soal_ids)) {
        return BankSoal::whereIn('id', $this->soal_ids)
            ->with(['jawaban', 'stimulus'])->get();
    }
    return BankSoal::where('paket_id', $this->paket_tryout_id)
        ->where('mapel_id', $this->mapel_id)
        ->with(['jawaban', 'stimulus'])
        ->inRandomOrder()
        ->limit($this->jumlah_soal)->get();
}
```

### Untuk Rumus Matematika
RichEditor (Trix) tidak mendukung LaTeX. Opsi:
1. Upload gambar rumus
2. Integrasi MathJax (butuh konfigurasi frontend)
3. Ganti editor ke TinyMCE/CKEditor dengan plugin equation
