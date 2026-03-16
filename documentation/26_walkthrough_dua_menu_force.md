# Walkthrough - Penerapan 2 Menu Force Ujian & State Tracking

Semua fondasi backend dan frontend untuk kontrol ujian dinamis telah sukses diimplementasikan.

---

## 🚀 Fitur yang Diterapkan

### 1. Database State Tracking
*   **Migration**: Kolom `current_mapel_id` berhasil ditambahkan ke tabel `peserta_jadwal` untuk mencatat progres mapel di sisi server.

### 2. Model & Logic (`PesertaJadwal.php`)
*   **calculateAndSubmit($isFinalSubmit)**:
    *   Mengkalkulasi total nilai secara akumulatif.
    *   Memiliki **Auto-Fallback**: Jika force pindah mapel dilakukan di mapel terakhir, otomatis mengunci total ujian.
    *   **Timer Reset**: Menyesuaikan timer `sisa_waktu` dengan durasi mapel baru dan memperbarui `waktu_mulai`.
    *   Dibungkus dalam **DB Transaction**.

### 3. Controller Guards (`StudentController.php`)
*   **simpanJawaban()**: Menolak penyimpanan data jika status sudah `completed` atau `mapel_id` tidak sesuai dengan progres saat ini karena pergeseran sepihak oleh Admin. Mengembalikan parameter `force_reload`.
*   **mulai()**: Menginisialisasi `current_mapel_id` ke mapel urutan pertama.
*   **submit()**: Disederhanakan untuk memanggil method model tunggal.

### 4. Frontend Dynamic Reload (`soal.blade.php`)
*   Fungsi `saveAnswer()` me-lempar parameter `mapel_id` saat fetch data.
*   Tangkap status HTTP `403` untuk melakukan **`window.location.reload()`** secara instan di layar siswa jika terjadi pergeseran kontrol dari Admin.

---

## 💻 Visualisasi Dashboard
Halaman Admin (`PesertaJadwalResource`) kini memiliki 2 Action dinamis:
1.  **Force Selesai Total** (Warna Merah)
2.  **Force Lanjut Mapel** (Warna Kuning)

---

## ✅ Cara Verifikasi
1.  Silakan coba masuk ke dalam ujian siswa.
2.  Dari monitor Admin, coba tekan tombol **"Force Lanjut Mapel"** atau **"Force Selesai Total"**.
3.  Perhatikan visualisasi interface pada layar siswa yang akan langsung berpindah panel.
