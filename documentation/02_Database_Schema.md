# 02. Desain Database (Schema)

Database: **PostgreSQL** — nama database: `tryout_tka`

---

## A. Tabel Master

### 1. `ref_mapel` (Mata Pelajaran)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BigInt PK | Auto-increment |
| `nama_mapel` | String | Bahasa Indonesia, Matematika, dll |
| `kode_mapel` | String, Unique | IND, MTK, LIT |
| `jenjang` | Enum | SD, SMP, SMA, UMUM |
| `created_at`, `updated_at` | Timestamp | |

### 2. `paket_tryout` (Paket Ujian)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BigInt PK | |
| `nama_paket` | String | "Tryout Akbar 2026" |
| `keterangan` | Text, Nullable | Deskripsi paket |
| `created_at`, `updated_at` | Timestamp | |

### 3. `paket_tryout_mapel` (Pivot: Paket ↔ Mapel)
Menghubungkan paket dengan mapel-mapel yang ada di dalamnya.
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BigInt PK | |
| `paket_tryout_id` | FK → paket_tryout | |
| `mapel_id` | FK → ref_mapel | |
| `jumlah_soal` | Integer | Jumlah soal untuk mapel ini |
| `waktu_mapel` | Integer | Durasi per mapel (menit) |
| `mode_soal` | Enum | `ACAK` / `MANUAL` |
| `soal_ids` | JSON, Nullable | ID soal spesifik jika mode MANUAL |
| `urutan` | Integer | Urutan tampil mapel |
| `created_at`, `updated_at` | Timestamp | |

---

## B. Tabel Bank Soal

### 4. `bank_stimulus` (Wacana/Stimulus)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BigInt PK | |
| `mapel_id` | FK → ref_mapel | |
| `paket_id` | FK → paket_tryout | |
| `judul` | String | Judul stimulus |
| `konten` | LongText/HTML | Isi wacana (RichEditor) |
| `tipe` | Enum | TEKS, AUDIO, VIDEO, GAMBAR |
| `media_path` | String, Nullable | Path file media |
| `created_at`, `updated_at` | Timestamp | |

### 5. `bank_soal` (Butir Pertanyaan)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BigInt PK | |
| `paket_id` | FK → paket_tryout | |
| `mapel_id` | FK → ref_mapel | |
| `stimulus_id` | FK → bank_stimulus, **Nullable** | Null = soal mandiri |
| `tipe_soal` | Enum | `PG_TUNGGAL`, `PG_KOMPLEKS`, `BENAR_SALAH` |
| `pertanyaan` | LongText/HTML | Konten soal |
| `pembahasan` | LongText, Nullable | Pembahasan (tampil setelah review) |
| `bobot` | Integer | Bobot default soal |
| `nomor_urut` | Integer | Custom sorting |
| `created_at`, `updated_at` | Timestamp | |

**Relasi:**
- `jawaban()` → hasMany `BankJawaban`
- `stimulus()` → belongsTo `BankStimulus`
- `mapel()` → belongsTo `RefMapel`
- `paket()` → belongsTo `PaketTryout`

### 6. `bank_jawaban` (Opsi Jawaban)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BigInt PK | |
| `soal_id` | FK → bank_soal | |
| `teks_jawaban` | Text | Teks pilihan jawaban |
| `skor` | Integer | Skor jika dipilih (+/- nilai) |
| `kunci_jawaban` | String, Nullable | Flag benar/salah |
| `label` | Char, Nullable | A, B, C, D, E (untuk UI) |
| `created_at`, `updated_at` | Timestamp | |

---

## C. Tabel Transaksi

### 7. `jadwal_tryout` (Jadwal Ujian)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BigInt PK | |
| `paket_tryout_id` | FK → paket_tryout | |
| `nama_jadwal` | String | "Sesi 1 - Pagi" |
| `tanggal` | Date | Tanggal pelaksanaan |
| `waktu_mulai` | Time | Jam mulai |
| `waktu_selesai` | Time | Jam selesai |
| `token` | String | Token akses (6 karakter) |
| `status` | Enum | active, inactive |
| `created_at`, `updated_at` | Timestamp | |

### 8. `peserta_jadwal` (Peserta ↔ Jadwal)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BigInt PK | |
| `user_id` | FK → users | |
| `jadwal_tryout_id` | FK → jadwal_tryout | |
| `token_used` | String | Token yang digunakan peserta |
| `status` | Enum | `registered`, `started`, `completed` |
| `waktu_mulai` | DateTime, Nullable | Waktu mulai mengerjakan |
| `waktu_selesai` | DateTime, Nullable | Waktu selesai |
| `sisa_waktu` | Integer | Sisa waktu (menit) |
| `total_nilai` | Decimal, Nullable | Nilai akhir |
| `created_at`, `updated_at` | Timestamp | |

### 9. `jawaban_peserta` (Detail Jawaban)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BigInt PK | |
| `peserta_jadwal_id` | FK → peserta_jadwal | |
| `bank_soal_id` | FK → bank_soal | |
| `jawaban` | JSON | Jawaban user (string untuk PG, array untuk PG_KOMPLEKS) |
| `is_ragu` | Boolean | Status ragu-ragu |
| `created_at`, `updated_at` | Timestamp | |

**Cast di Model:** `'jawaban' => 'array'`, `'is_ragu' => 'boolean'`

---

## D. Entity Relationship Diagram

```
ref_mapel ──┬── bank_stimulus
            ├── bank_soal ──── bank_jawaban
            └── paket_tryout_mapel

paket_tryout ──┬── paket_tryout_mapel
               ├── bank_soal
               ├── bank_stimulus
               └── jadwal_tryout ──── peserta_jadwal ──── jawaban_peserta

users ──── peserta_jadwal
```
