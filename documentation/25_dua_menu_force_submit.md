# Implementation Plan - 2 Menu Kontrol Ujian (Revised with Gotchas)

Tujuan: Mengimplementasikan kendali kemudi ujian dinamis (**Force Akhiri Ujian** & **Force Lanjut Mapel**) dengan penanganan edge-case di tingkat model dan controller.

---

## 📅 Rencana Perubahan Kode & Gotchas

### 1. Buat Migration Kolom Baru [NEW]
*   Menambah kolom `current_mapel_id` (nullable, unsignedBigInteger) ke tabel `peserta_jadwal`.
*   *Command prompt*: `php artisan make:migration add_current_mapel_id_to_peserta_jadwal`

### 2. Modifikasi Model `PesertaJadwal.php` [MODIFY]
*   **Fungsi `calculateAndSubmit($isFinalSubmit = true)`**:
    *   **Logic Fallback**: Jika `$isFinalSubmit = false`, cari id mapel urutan selanjutnya. Jika hasil buntu (null/sudah mapel terakhir), otomatis setel `$isFinalSubmit = true`.
    *   **Logic Reset Timer**: Jika lanjut mapel, lakukan pembaruan kolom `sisa_waktu` mengikuti sisa alokasi waktu mapel berikutnya (supaya timer reset menuruti mapel baru), dan `waktu_mulai = now()`.
    *   **DB Transaction**: Semua dibungkus `DB::transaction()` agar aman.

### 3. Modifikasi `StudentController.php` [MODIFY]
*   **Optimalisasi `simpanJawaban()`**: Tambahkan parameter verification: jika `$request->mapel_id != $pesertaJadwal->current_mapel_id` (karena baru diganti Admin), return response JSON `status: 'force_reload'`.
*   **Inisialisasi `mulai()`**: Isi data `$pesertaJadwal->current_mapel_id` mengarah pada mapel urutan ke-1 saat pertama start.

### 4. Modifikasi `PesertaJadwalResource.php` [MODIFY]
*   Split buttons ke tombol: **Force Akhiri Ujian Total** (`true`) & **Paksa Lanjut Mapel** (`false`).

---

## ✅ Rencana Verifikasi (Manual)

1.  Jalankan migration.
2.  Mulai ujian, klik "Force Lanjut Mapel" dari Admin.
3.  Di layar siswa, klik Isian jawaban klick berikutnya, niscaya notif berganti dan layar otomatis ter-*refresh* mengarah ke Mapel 2.
