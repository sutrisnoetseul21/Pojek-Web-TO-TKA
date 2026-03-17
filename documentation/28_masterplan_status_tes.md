# Masterplan - Menu Status Tes (Manajemen Proktor)

Menu ini berfungsi untuk melihat konfigurasi tes yang aktif hari ini, mengaktifkan kelompok tes, dan merilis token ujian.

---

## 🔍 1. Tampilan & Kolom Informasi

Tabel **Status Tes** menampilkan ringkasan data penting yang diambil dari Jadwal Tryout:

| Kolom | Deskripsi / Fungsi |
| :--- | :--- |
| **Kode Tes** | Identitas kode tes yang akan diberikan kepada peserta. |
| **Waktu Tes** | Tanggal & jendela waktu (buka - tutup) rilis token. |
| **Waktu Perpanjangan** | Informasi tambahan waktu buka-tutup jika ada pertambahan waktu. |
| **Kelompok Tes** | Informasi nomor/nama kelompok tes yang sedang berjalan. |
| **Set** | Tombol aksi untuk **mengaktifkan** kelompok/sesi tes. |
| **Token Tes** | Menampilkan Token (6 digit/karakter) atau tombol **Rilis Token**. |

---

## ⚙️ 2. Aturan & Logika Rilis Token

Keaktifan tombol **Rilis Token** (Release Token) dikontrol oleh Status Pusat/Helpdesk:

1.  🔵 **Warna Biru**: Akses token dibuka Pusat **DAN** sudah masuk waktu buka token. Proktor dapat **mengklik tombol** untuk merilis token.
2.  ⚪ **Warna Abu-abu**: Akses dibuka Pusat, **TETAPI** belum masuk waktu buka token. Proktor belum dapat mengklik tombol.
3.  ➖ **Tanda Strip (-)**: Akses token **belum dibuka** oleh Helpdesk Pusat/Provinsi.

---

## 🛠️ 3. Alur Kerja Proktor (Menjalankan Ujian)

Untuk memulai ujian, alurnya harus melompati dua menu secara teratur:

1.  **Status Tes ➡️ Tombol SET**: Klik tombol `SET` (warna biru) pada kelompok yang akan ujian, untuk mengaktifkan sesi. (Konfirmasi: Yes).
2.  **Status Tes ➡️ Rilis Token**: Setelah tombol aktif, klik `Rilis Token` untuk menampilkan Token.
    *   *Catatan*: Token harus diperbarui (diklik ulang) setiap **15 menit sekali**.
3.  **Kelompok Tes ➡️ Assign**: Setelah Token keluar, pindah ke menu `Kelompok Tes` dan lakukan `Assign` kepada peserta (seperti direncanakan di rencana Masterplan Kelompok Tes).

---

## 💾 4. Estimasi Kebutuhan Teknis (Filament & Database)

1.  **Struktur Data** (Estimasi):
    *   Tabel `jadwal_tryouts` (kebutuhan `token`, `released_at`, `is_active`).
    *   Tabel `setting_token` (untuk sync setting pusat).
2.  **Interface (Filament Custom Column)**:
    *   `Set` custom action button.
    *   `Token` custom action button (Toggle `Rilis Token` 🆚 Token string).

---

*Dokumen ini merupakan bagian dari Masterplan sekuensial pengerjaan menu Proktor fase pertama.*
