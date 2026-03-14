# Dokumentasi Fitur: Monitoring Ujian & Laporan Hasil

> **Tanggal**: 14 Maret 2026
> **Status**: Selesai (Completed)

## 1. Ringkasan

Penambahan dua kelompok menu baru dan penguatan asosiasi data pada menu yang sudah ada:

0. **Kematangan Paket & Jadwal** — Selesai (Completed)
1. **Monitoring Ujian** — Memantau ujian secara real-time.
2. **Laporan / Hasil** — Melihat hasil ujian setelah selesai.

---

## 3. Penguatan Paket & Jadwal (Prerequisite)

> **Status**: Selesai (Completed)

### A. Paket Tryout
- **Sekolah Terpilih**: Paket soal kini bisa dikunci untuk satu sekolah tertentu. Jika dikosongkan, berarti paket bersifat global (nasional).
- **Auto-Filter**: Admin sekolah hanya melihat paket yang ditujukan untuk sekolah mereka atau paket global.

### B. Jadwal Tryout
- **Target Sekolah & Kelas**: Saat membuat jadwal, admin/super-admin memilih satu sekolah dan satu atau lebih kelas.
- **Auto-Assignment**: Siswa yang berada di kelas terpilih otomatis didaftarkan ke jadwal tersebut (masuk ke tabel `peserta_jadwal`) tanpa perlu diinput manual satu per satu.

---

## 4. Struktur Menu Baru

### A. Monitoring Ujian (Navigation Group)

| Menu | Deskripsi |
|------|-----------|
| **Peserta Sedang Tes** | Memantau peserta yang sedang aktif mengerjakan ujian. Filter: Sekolah → Jadwal Aktif. |
| **Monitoring Sesi** | Ringkasan pelaksanaan ujian per sekolah/jadwal. Menampilkan jumlah peserta per status. |
| **Log Aktivitas** | Catatan kejadian penting selama ujian (login, mulai, submit, timeout, disconnect, force submit). |
| **Bantuan Peserta** | Tindakan cepat: reset sesi, izinkan login ulang, force submit, perpanjang waktu. |

### B. Laporan / Hasil (Navigation Group)

| Menu | Deskripsi |
|------|-----------|
| **Hasil Sementara** | Progres pengerjaan peserta saat ujian masih berlangsung. |
| **Hasil Tryout** | Hasil akhir ujian: nilai, ranking, rekap per kelas/sekolah. |

---

## 3. Hak Akses Baru

| Permission | Label UI | Deskripsi |
|------------|----------|-----------|
| `manage_monitoring` | 🔍 Kelola Monitoring Ujian | Akses ke Peserta Sedang Tes, Monitoring Sesi, Log Aktivitas, Bantuan Peserta |
| `manage_laporan` | 📊 Kelola Laporan & Hasil | Akses ke Hasil Sementara dan Hasil Tryout |

Total permission admin menjadi **5**:
1. `manage_peserta` — Kelas & Peserta
2. `manage_soal` — Bank Soal
3. `manage_ujian` — Paket Tryout & Jadwal
4. `manage_monitoring` — Monitoring Ujian *(baru)*
5. `manage_laporan` — Laporan & Hasil *(baru)*

---

## 4. Arsitektur Data

### Tabel yang sudah ada dan relevan:
- `peserta_jadwal`: Pivot user↔jadwal dengan `status` (registered/started/completed), `waktu_mulai`, `waktu_selesai`, `sisa_waktu`, `total_nilai`.
- `jawaban_peserta`: Jawaban per soal per peserta.
- `jadwal_tryout`: Jadwal ujian dengan scope `active`, `ongoing`, `upcoming`.
- `users`: Data peserta dengan relasi `sekolah_id`, `kelas_id`.

### Perubahan yang diperlukan:
- **Migration**: Tambah status `timeout` dan `disconnected` pada enum `peserta_jadwal.status`.
- **Migration**: Buat tabel `ujian_activity_log` untuk Log Aktivitas.
- **Migration**: Buat tabel `ujian_bantuan_log` untuk riwayat bantuan peserta.

---

## 5. Detail Alur Filter Tiap Menu

```text
Semua menu mengikuti pola:
Sekolah → Tes/Jadwal → (Opsional: Kelas/Peserta) → Data
```

### Peserta Sedang Tes
Kolom: Nama, Username, Kelas, Sekolah, Jadwal, Waktu Mulai, Sisa Waktu, Status
Status: belum_mulai, sedang_tes, selesai, timeout, terputus

### Monitoring Sesi
Kolom: Sekolah, Jadwal, Waktu, Terdaftar, Belum Mulai, Sedang, Selesai, Bermasalah

### Log Aktivitas
Kolom: Waktu, Peserta, Kelas, Sekolah, Jenis Aktivitas, Keterangan
Contoh: login, mulai_ujian, simpan_jawaban, submit, logout, timeout, disconnect, login_ulang, force_submit

### Bantuan Peserta
Tindakan: Reset sesi, Izinkan login ulang, Buka ulang akses, Force submit, Perpanjang waktu, Sinkron status
Kolom: Nama, Kelas, Sekolah, Status Ujian, Waktu Mulai, Sisa Waktu, Riwayat Bantuan

### Hasil Sementara
Kolom: Nama, Kelas, Soal Terjawab, Belum Dijawab, Status, Waktu Mulai, Nilai Sementara

### Hasil Tryout
Kolom: Nama, Kelas, Sekolah, Total Nilai, Ranking, Benar, Salah, Kosong, Waktu, Status
Rekap: Per Kelas, Per Sekolah, Tertinggi, Terendah, Rata-rata

---

*Dokumentasi ini akan diperbarui setelah implementasi selesai.*
