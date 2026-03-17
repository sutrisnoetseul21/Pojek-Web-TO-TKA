# Masterplan - Kontrol Aktivasi Token (Sinkronisasi dengan Status Tes)

Menambahkan fitur kunci/aktifasi token di tingkat backend (`Jadwal Tryout`) untuk mencegah rilis token tidak sengaja oleh proktor seandainya waktu belum tiba atau sudah lewat.

---

## 🔍 1. Kebutuhan Struktur Data (Database)

| Kolom Baru | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| **`is_token_active`** | `boolean` (`default: true`) | Menandakan apakah token pada jadwal ini legal/diizinkan untuk dirilis oleh Proktor. |

---

## ⚙️ 2. Integrasi ke Jadwal Tryout (Admin/Super Admin)

Form input di **`JadwalTryoutResource.php`** akan ditambahkan saklar:
- `Toggle::make('is_token_active')->label('Izinkan Rilis Token')->default(true)`
- **Fungsi**: Jika dimatikan (`false`), Token tidak akan bisa "Dirilis" di layar monitoring ujian (Status Tes).

---

## 🛠️ 3. Sinkronisasi di Status Tes (Layar Proktor)

Logika tombol **Rilis Token** di `StatusTes.php` diatur ulang:

### A. Logika Kunci (Disabling)
Tombol `Rilis Token` akan **Otomatis Non-aktif (Disabled)** dalam kondisi berikut:
1.  Pengaturan Global Pusat mati (`is_token_release_enabled = false`).
2.  Waktu Tes belum masuk rentang (`now()` di luar start/end).
3.  **[KONDISI BARU]** `is_token_active` di detail jadwal bernilai **`false`** (Dinonaktifkan Admin).

### B. Tampilan Label & Warna
Jika saklar dimatikan (`false`), tombol `Rilis Token` akan menunjukkan keterangan **`🔒 Token Belum Rilis`** (Mencegah salah klik) dengan warna abu-abu / disabled state.

---

*Dokumen ini merupakan bagian dari usulan sinkronisasi fitur Token.*
