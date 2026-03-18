# Master Plan - Menu Daftar Login (Reset Sesi)

Menu **Daftar Login** akan dirancang sebagai interface ringan (*lighter interface*) khusus bagi pengawas (Proktor) untuk melacak peserta yang **sedang mengerjakan tes** dan membebaskan kendala "Sesi Terkunci" secara cepat.

---

## 🎯 1. Model & Query Dasar
*   **Target Model**: `PesertaJadwal` (Sama seperti Bantuan Peserta).
*   **Filter Dasar (Strict)**: 
    Hanya mengambil peserta dengan status **`started`** atau **`working`** (Sedang Berjalan). 
    *Peserta yang belum mulai (`registered`) atau sudah selesai (`completed`) tidak akan masuk ke interface ini.*

---

## 📊 2. Desain Layout Tabel (Filament Table)
Kami akan mentransformasikan `DaftarLogin.php` menjadi tabel responsif Filament demi efisiensi fungsionalitas pencarian, cekbox, dan aksi massal:

| Kolom | Binding Data | fungsionalitas |
| :--- | :--- | :--- |
| **Checkbox** | Standar Filament | Mendukung aksi massal (*Bulk Action*). |
| **Username** | `user.username` | Terintegrasi dengan Fitur **Pencarian (Searchable)**. |
| **Password** | `user.plain_password` | Menampilkan password "terbuka" untuk bantuan login cepat siswa. |
| **Sisa Waktu** | `sisa_waktu` | Digayakan dengan warna (Merah jika < 5 Menit). |

---

## ⚡ 3. Tombol Aksi (Single & Bulk)
Akan ditambahkan dua pemicu fungsi Reset Sesi:

### A. Tombol Baris (Row Action: "Reset")
Terletak di sisi kanan baris tabel.
*   **Logika**:
    ```php
    \Illuminate\Support\Facades\DB::table('sessions')
        ->where('user_id', $record->user_id)
        ->delete();
    ```
    *Membatalkan kunci session, membiarkan status tetap `started` agar ujian tidak terganggu/terulang.*

### B. Tombol Massal (Bulk Action: "Reset Peserta")
Aktif otomatis di header tabel jika ada kotak cekbox yang dicentang.
*   **Logika**: Mengulang loop logika hapus sesi di atas pada seluruh `$records` yang terpilih.

---

Dengan master plan ini, proktor hanya perlu waktu 1 Detik untuk melepas klem *Concurrent Login* siswa ketika PC mereka tiba-tiba mati / blank tanpa merusak progres pengerjaan ujian.

**Apakah rancangan ini dapat disetujui untuk mulai dikoding?**
