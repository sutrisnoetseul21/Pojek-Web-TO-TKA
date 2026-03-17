# Walkthrough - Refactor Mode Kategori & Visibilitas Tabel

Menambahkan fleksibilitas penentuan komposisi soal tryout per-kategori menggunakan struktur **Nested Repeater** di dalam Mata Pelajaran, serta mengontrol visibilitas kolom tabel.

---

## 🛠️ 1. Mode Pemilihan per Kategori (Struktur Baru)

Sebelumnya, semua kategori dalam satu Mapel berbagi satu Mode global (Acak/Manual). Sekarang, tiap kategori memiliki konfigurasinya sendiri.

### A. Database & Model (`PaketTryoutMapel.php`)
*   **Migrasi**: Menambahkan kolom `kategori_settings` (JSON) ke tabel `paket_tryout_mapel`.
*   **Casting**: Menambahkan `'kategori_settings' => 'array'` ke dalam `$casts`.
*   **Query**: Memperbarui method `getSoal()` agar membaca Loop array objek `kategori_settings` (menggabungkan hasil query ACAK, MANUAL, atau SEMUA per kategori).

### B. Dashboard Form (`PaketTryoutResource.php`)
*   Mengganti `CheckboxList` kategori dengan **Repeater** (`kategori_settings`).
*   Menerapkan binding **`->live()`** pada Select Kategori dan Mode, sehingga sub-form manual (Checkbox Picker Soal) langsung memuat data yang tepat ketika Kategori terpilih.

---

## 📊 2. Visibilitas & Pelengkap Tabel Indeks

Untuk merapikan daftar list, dilakukan penyesuaian di interface index:

### A. Paket Tryout (`PaketTryoutResource.php`)
*   **Kolom Kode Paket**: Ditambahkan kolom `Tables\Columns\TextColumn::make('kode')` agar pengenal sistem terlihat di depan.
*   **Kolom Sekolah**: Menambahkan logika `->visible(fn () => auth()->user()->isSuperAdmin())` agar kolom ini tersembunyi bagi Admin biasa.

### B. Jadwal Tryout (`JadwalTryoutResource.php`)
*   **Kolom Sekolah**: Menambahkan logika visibilitas yang sama `->visible(fn () => auth()->user()->isSuperAdmin())` demi menyelaraskan layout interface.

---

*Dokumen ini mencatat pembaruan sistem komposisi soal bercabang dan hak akses visual kolom.*
