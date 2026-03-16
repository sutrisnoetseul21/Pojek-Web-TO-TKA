# Implementation Plan - Fix Force Submit dengan DB Transaction (Revised)

Tujuan: Memastikan tombol **"Force Submit"** Admin melakukan kalkulasi nilai yang sama persis dengan Submit normal Siswa, dibungkus secara aman demi konsistensi data, serta mengamankan sisi Frontend.

---

## 📅 Rencana Perubahan Kode

### 1. Buat Method Model `PesertaJadwal.php` [MODIFY]
*   **Tujuan**: Merawat logic kalkulasi di satu tempat (DRY), dibungkus **DB Transaction** untuk menjaga atomicity.
*   **Isi Fungsi**:
    1. `DB::beginTransaction()`.
    2. Looping `JawabanPeserta`, match scoring PG, Kompleks, BS.
    3. Update `total_nilai`.
    4. Set `status = 'completed'` & `waktu_selesai`.
    5. `DB::commit()`.

---

### 2. Update `StudentController.php` [MODIFY]
*   **A. fungsi `simpanJawaban` (Guard)**:
    *   Tambahkan filter: Jika `$pesertaJadwal->status === 'completed'`, respon ERROR json agar AJAX menolak menyimpan dan mengarahkan siswa keluar ke dashboard dengan pesan peringatan.
*   **B. fungsi `submit` (DRY)**:
    *   Ganti loop kalkulasi nilai 70 baris yang ada saat ini dengan satu baris pemanggilan model kustom: `$pesertaJadwal->calculateAndSubmit()`.

---

### 3. Update `PesertaJadwalResource.php` [MODIFY]
*   Aksi `force_submit` dirombak untuk memanggil `$record->calculateAndSubmit()` ketimbang sekadar `status = completed`.

---

## ✅ Rencana Verifikasi (Manual)

Untuk memvalidasi visual & backend:

1.  **Layar Siswa**: Klik "Force Submit" dari Admin saat siswa sedang mengerjakan.
2.  **Siswa Klik Next**: Pastikan siswa mendapatkan respon error/redirect bahwa *"Sesi telah ditutup oleh pengawas"*.
3.  **Kecocokan Nilai**: Cek apakah nilai siswa di halaman Hasil Admin/Siswa terhitung secara utuh (tidak 0 lagi).
