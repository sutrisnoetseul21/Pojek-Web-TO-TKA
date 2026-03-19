# Masterplan: Pembuatan Peran Proktor & Pembatasan Dashboard

Rencana implementasi untuk memisahkan akun Super Admin dengan akun **Pengawas Ruangan (Proktor)** agar menu monitoring ujian aman dari penyalahgunaan.

---

## 🎯 Tujuan Utama
Menambahkan peran (*Role*) **Proktor** yang **HANYA** bisa melihat dan mengelola menu dari grup `Monitoring Ujian` (Status Peserta, Reset Login, dll), dan **TIDAK BISA** melihat bank soal, mengedit Paket Tryout, atau mengubah pengaturan sistem.

---

## 🛠️ Rencana Perubahan

### 1. 🔑 Pendataan Akun & Role (Database)
Kita akan memastikan file seeder atau migration siap menambahkan hak akses khusus ini.
- **Nama Peran**: `proctor` atau `pengawas`.
- **Daftar Hak Akses (Permissions)**:
  - `view_monitoring` (Wajib)
  - `reset_login` (Wajib)
  - `force_submit_ujian` (Wajib)
  - *Dilarang*: `manage_questions`, `manage_users`, `manage_settings`.

### 2. 🛡️ Penyesuaian `canAccess()` di Pages Filament
Kita akan memodifikasi fungsi pengaman (`canAccess`) di halaman-halaman yang ada di sidebar.

#### 📂 File Kelompok *"Monitoring Ujian"* 
*(StatusPeserta.php, DaftarLogin.php, RequestReset.php, KelompokTes.php)*
- **Modifikasi**: Membolehkan login admin yang memiliki hak proktor atau superadmin.
```php
public static function canAccess(): bool
{
    $user = auth()->user();
    return $user->hasRole('super_admin') || $user->hasPermissionTo('view_monitoring');
}
```

#### 📂 File Kelompok *"Manajemen Soal" / "Data Master"*
- **Modifikasi**: Mengunci rapat agar proktor **TIDAK** bisa masuk.
```php
public static function canAccess(): bool
{
    return auth()->user()->hasRole('super_admin'); // Hanya SuperAdmin
}
```

### 3. 📊 Pembagian Wilayah Kerja (Data Isolation)
*(Sifat: Opsional / Pengembangan Lanjutan)*
Jika sekolah ingin Proktor A hanya melihat Kelas-X dan Proktor B hanya melihat Kelas-XI:
- Kita akan menambahkan Filter Query `whereHas('user.kelas_id', ...)` di dalam method `table()` masing-masing halaman monitoring tersebut agar data yang ditarik terseleksi otomatis berdasarkan alokasi kelas proktor.

### 4. 👥 Manajemen User Multi-Tenant (Admin Sekolah ➔ Proktor)
Untuk membatasi kewenangan Admin Sekolah saat mengelola akun dalam sekolah mereka sendiri, diterapkan tiga tingkat keamanan pada `UserResource`:

*   **🔑 1. Isolasi Daftar Pengguna (Read)**:
    *   **Deskripsi**: `Admin` Sekolah A tidak boleh melihat `Proktor` atau `Peserta` dari Sekolah B.
    *   **Solusi**: Override fungsi `getEloquentQuery()` untuk mem-filter data berdasarkan `sekolah_id` admin yang sedang login.
*   **🛡️ 2. Pembatasan Pilihan Role (Security)**:
    *   **Deskripsi**: Admin Sekolah tidak boleh membuat/menugaskan akun `Super Admin` atau `Admin` lain.
    *   **Solusi**: Manipulasi dropdown `Select` Role agar hanya menampilkan pilihan role `proktor` dan `peserta` apabila yang login adalah Admin Sekolah.
*   **🔗 3. Injeksi ID Sekolah Otomatis (Write)**:
    *   **Deskripsi**: Admin Sekolah tidak perlu memilih sekolah (mengurangi-human error). Sistem otomatis menempelkan `sekolah_id` mereka.
    *   **Solusi**: Gunakan komponen `Hidden` (tersembunyi) yang mengambil `sekolah_id` dari Admin login, sementara `Super Admin` tetap dapat memilih sekolah.

---

## 💻 Panduan Eksekusi (Untuk AI Agent)
*Bapak/Developer bisa langsung menyalin instruksi ini ke AI Agent untuk diterapkan pada `UserResource`:*

### Implementation Plan: Multi-Tenant User Management (Admin -> Proktor)

We need to empower `admin` to create `proktor` (and `peserta`) accounts, but strictly isolate this process so they can only manage users within their own `sekolah_id`.

#### Task: Modify `app/Filament/Resources/UserResource.php`

**1. Isolate the Table Query (View Restriction):**
Override the `getEloquentQuery()` method so `admin` only sees their own school's users.
```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    
    // Check using Spatie hasRole()
    if (auth()->user()->hasRole('admin_sekolah') || auth()->user()->hasRole('admin')) {
        $query->where('sekolah_id', auth()->user()->sekolah_id);
    }
    
    return $query;
}
```

**2. Restrict Role Assignment (Security Guard):**
In the `form(Form $form)` method, update the Roles Select component. `admin` MUST NOT be able to create Super Admins or other Admins.
```php
use Illuminate\Database\Eloquent\Builder;

Forms\Components\Select::make('roles')
    ->relationship('roles', 'name', modifyQueryUsing: function (Builder $query) {
        if (auth()->user()->hasRole('admin_sekolah') || auth()->user()->hasRole('admin')) {
            // Admin can only create Proctors and Students
            return $query->whereIn('name', ['proktor', 'peserta']);
        }
        return $query;
    })
    ->preload()
    ->searchable()
```

**3. Auto-Inject `sekolah_id` (Hidden Injection):**
In the `form(Form $form)` method, handle the `sekolah_id` field dynamically.
```php
Forms\Components\Select::make('sekolah_id')
    ->relationship('sekolah', 'nama_sekolah')
    ->visible(fn () => auth()->user()->hasRole('super_admin')) // Only SuperAdmin can choose
    ->required(fn () => auth()->user()->hasRole('super_admin')),

Forms\Components\Hidden::make('sekolah_id')
    ->default(fn () => auth()->user()->sekolah_id)
    ->visible(fn () => auth()->user()->hasRole('admin_sekolah') || auth()->user()->hasRole('admin')), // Auto-fill for Admin
```

---

## ✅ Rencana Verifikasi (Review)

1. **Tes Login Super Admin**: Memastikan semua menu (Bank Soal, User, Sekolah, Monitoring) tetap muncul lengkap tanpa eror.
2. **Tes Login Proktor**: Memastikan sidebar **HANYA** menampilkan grup `Monitoring Ujian` dan `Dashboard` dasar. Menu Bank Soal dan Setting harus hilang total.
3. **Tes Eksekusi**: Memastikan Proktor tetap bisa klik `Reset Login` dan data laporannya tersimpan di tabel log aktivitas dengan record username milik pribadi mereka.

---

> [!NOTE]
> Pemisahan ini membutuhkan pembuatan data Role di database. Jika bapak menyetujui rencana diagram di atas, kita bisa siapkan script command php artisan untuk generate role otomatisnya.
