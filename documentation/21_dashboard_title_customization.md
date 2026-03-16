# Implementation Plan - Custom Dashboard & Style Cleanup

Tujuan: Mengubah Judul Dashboard dinamis (memperlihatkan nama Sekolah jika Admin) dan membersihkan widget default Filament agar panel admin terlihat lebih siap-pakai dan profesional.

---

## 📅 Perubahan Kode

### 1. Buat Custom Dashboard Page [NEW]
**File**: `app/Filament/Pages/Dashboard.php`

*   **Tujuan**: Meng-override judul halaman dashboard secara dinamis.
*   **Logika Aman**: Menggunakan `?->` (Nullsafe) serta fallback value untuk mencegah *Attempt to read property on null*.

**Implementasi Kode**:
```php
namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    public function getTitle(): string | Htmlable
    {
        $user = auth()->user();

        if ($user?->role === 'admin') {
            return $user->sekolahRelation?->nama_sekolah ?? 'Dashboard Ujian';
        }

        return 'Dashboard';
    }
}
```

---

### 2. Modifikasi Administrasi Panel Provider [MODIFY]
**File**: `app/Providers/Filament/AdminPanelProvider.php`

*   **Tujuan**: Menghilangkan widget default bawaan & set identity aplikasi.
*   **Langkah-langkah**:
    1.  **Ganti Judul Brand**: Tambahkan `->brandName('CBT Excellent')` pada panel builder.
    2.  **Override Halaman Pages**: Pada array `->pages([])`, ganti `Pages\Dashboard::class` menjadi `\App\Filament\Pages\Dashboard::class`.
    3.  **Hapus Header Widget**: Kosongkan isi array `->widgets([...])` (hapus `AccountWidget` dan `FilamentInfoWidget`).

---

## ✅ Rencana Verifikasi (Manual)

Untuk memvalidasi visual bekerja, silakan buka Dashboard Admin (`/admin`):

1.  **Dashboard Title**: Login menggunakan user `admin` dan cek apakah judul di header atas berubah menjadi "Dashboard CBT [Nama Sekolah]".
2.  **Brand Title**: Cek apakah logo kiri atas berubah dari "Laravel" menjadi "CBT Excellent".
3.  **Clean Widgets**: Pastikan box "Welcome" dan box "Filament Docs" telah hilang dari muka layar.
