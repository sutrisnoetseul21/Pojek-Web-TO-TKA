# 01. Arsitektur & Tech Stack

## Stack Teknologi (Aktual)

| Komponen | Teknologi | Versi | Keterangan |
| :--- | :--- | :--- | :--- |
| **Framework** | Laravel | 12.50.0 | Backend + routing + auth |
| **PHP** | PHP | 8.4.18 | Runtime |
| **Admin Panel** | FilamentPHP | v3.x | CRUD admin (Soal, Stimulus, Paket, Jadwal, User) |
| **Realtime UI** | Livewire | v3.x | Komponen interaktif di admin |
| **Database** | PostgreSQL | - | Database utama (`tryout_tka`) |
| **Frontend Student** | Blade + Vanilla JS | - | Tanpa framework JS, murni DOM manipulation |
| **CSS Student** | Vanilla CSS | - | Inline `<style>` per halaman (bukan Tailwind) |
| **Build Tool** | Vite | - | Asset bundling untuk admin panel |
| **Auth** | Laravel Session | - | Session-based authentication |

---

## Arsitektur Aplikasi

```
┌─────────────────────────────────────────────────────────┐
│                       BROWSER                           │
├────────────────────────┬────────────────────────────────┤
│   ADMIN (Filament)     │   STUDENT PORTAL (Blade+JS)   │
│   /admin/*             │   /tryout/*                    │
│   Livewire components  │   Vanilla JS + Fetch API       │
├────────────────────────┴────────────────────────────────┤
│                   Laravel 12 (PHP 8.4)                  │
│   ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│   │  Controllers  │  │   Models     │  │  Middleware   │ │
│   │  (Student)    │  │  (Eloquent)  │  │  (Auth,CSRF) │ │
│   └──────────────┘  └──────────────┘  └──────────────┘ │
├─────────────────────────────────────────────────────────┤
│                  PostgreSQL (tryout_tka)                 │
└─────────────────────────────────────────────────────────┘
```

---

## Fitur Utama & Solusi Teknis

### 1. Split Screen UX
*   Layout 2 kolom: Stimulus (kiri, sticky) + Soal/Jawaban (kanan).
*   Navigasi soal tidak reload halaman — semua data di-load sekali via `@json()`.
*   Stimulus otomatis muncul/hilang berdasarkan apakah soal punya `stimulus_id`.

### 2. Per-Mapel Timer & Sequential Flow
*   Setiap mapel dalam paket memiliki **waktu tersendiri** (`waktu_mapel` di `paket_tryout_mapel`).
*   Peserta harus **menyelesaikan mapel secara berurutan** (tidak bisa skip).
*   **Two-step confirmation** saat pindah mapel (peringatan soal dikunci).
*   Timer per mapel—jika habis, otomatis pindah ke mapel berikutnya.

### 3. Soal Group (Stimulus Sharing)
*   Soal yang mengacu ke stimulus yang sama (`stimulus_id`) ditampilkan dengan wacana di kolom kiri.
*   Saat navigasi antar soal satu stimulus, kolom kiri tidak reload.

### 4. Mode Soal: ACAK vs MANUAL
*   **ACAK**: Soal diambil random dari bank_soal berdasarkan `paket_id` + `mapel_id`.
*   **MANUAL**: Admin memilih soal spesifik via checkbox (tersimpan di `soal_ids` JSON).

---

## File Utama

| File | Fungsi |
|------|--------|
| `app/Http/Controllers/StudentController.php` | Controller utama student portal |
| `app/Models/PaketTryoutMapel.php` | Model pivot paket↔mapel dengan method `getSoal()` |
| `app/Models/BankSoal.php` | Model soal dengan relasi `jawaban`, `stimulus` |
| `app/Models/JawabanPeserta.php` | Model jawaban peserta (cast `jawaban` ke array) |
| `resources/views/student/soal.blade.php` | Halaman mengerjakan soal (terbesar, ~700 lines) |
| `resources/views/student/selesai.blade.php` | Konfirmasi selesai |
| `resources/views/student/hasil.blade.php` | Review hasil |
