# Dokumentasi Overhaul Sistem CBT: Arsitektur, Keamanan & Performa (v4 - Final)

> **Status**: ✅ Selesai (Selesai pada 14 Maret 2026)
> **Tanggal Update**: 14 Maret 2026
> **Prioritas**: Selesai

---

## 1. Latar Belakang

Overhaul besar ini dilakukan untuk:
- Meningkatkan **skalabilitas** sistem agar siap menangani ribuan peserta.
- Mengimplementasikan **hak akses granular** (Admin Soal vs Admin Peserta).
- Menerapkan **audit trail** untuk setiap aksi kritikal.
- Memperbaiki **struktur database** dari flat menjadi relasional (Sekolah → Kelas → Peserta).

---

## 2. Paket Tambahan (Dependencies)

| Package                        | Fungsi                                                |
|--------------------------------|-------------------------------------------------------|
| `spatie/laravel-permission`    | Role & Permission (RBAC) granular                     |
| `spatie/laravel-activitylog`   | Audit trail (log setiap aksi admin)                   |
| `spatie/laravel-backup`        | Backup database otomatis harian                       |
| `spatie/laravel-query-builder` | Filter & sorting performa tinggi untuk Filament       |

### Instalasi
```bash
composer require spatie/laravel-permission
composer require spatie/laravel-activitylog
composer require spatie/laravel-backup
composer require spatie/laravel-query-builder

php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"

php artisan migrate
```

---

## 3. Standarisasi Tipe Data (Enum)

### `App\Enums\UserRole`
```php
enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN       = 'admin';
    case PESERTA     = 'peserta';
}
```

### `App\Enums\Jenjang`
```php
enum Jenjang: string
{
    case SD   = 'SD';
    case SMP  = 'SMP';
    case SMA  = 'SMA';
    case SMK  = 'SMK';
    case UMUM = 'UMUM';
}
```

---

## 4. Struktur Database (Refactoring)

### A. Tabel Baru: `sekolah`

```php
Schema::create('sekolah', function (Blueprint $table) {
    $table->id();
    $table->string('nama_sekolah');
    $table->string('npsn', 8)->unique();   // Index otomatis via unique
    $table->text('alamat')->nullable();
    $table->softDeletes();
    $table->timestamps();

    $table->index('npsn');                 // Index tambahan untuk pencarian
});
```

### B. Tabel Baru: `kelas`

```php
Schema::create('kelas', function (Blueprint $table) {
    $table->id();
    $table->string('nama_kelas');           // Contoh: 10-A, 11-IPA-2
    $table->string('jenjang');              // Enum: SD, SMP, SMA, SMK, UMUM
    $table->foreignId('sekolah_id')->constrained('sekolah')->onDelete('cascade');
    $table->text('keterangan')->nullable();
    $table->softDeletes();
    $table->timestamps();

    $table->unique(['sekolah_id', 'nama_kelas']);  // Tidak boleh duplikat nama kelas per sekolah
    $table->index('sekolah_id');
});
```

### C. Modifikasi Tabel: `users`

```php
// Migration: update_users_table_overhaul
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('sekolah_id')->nullable()->constrained('sekolah')->nullOnDelete();
    $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
    $table->string('nomor_peserta')->unique()->nullable();
    $table->softDeletes();

    $table->index('sekolah_id');
    $table->index('kelas_id');
});
```

**Catatan**: Kolom `sekolah` (string), `npsn` (string), dan `jenjang` (string) yang lama di tabel `users` akan di-deprecate secara bertahap. Data akan dimigrasi ke relasi `sekolah_id` dan `kelas_id`.

### D. Tabel Pivot: `admin_kelas`

```php
Schema::create('admin_kelas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
    $table->timestamps();

    $table->unique(['user_id', 'kelas_id']);
});
```

### E. Soft Deletes pada Tabel Existing

```php
// Tambahkan $table->softDeletes() pada:
// - bank_soal
// - paket_tryout
// - jadwal_tryout
```

### F. Performance Indexing (Tabel Existing)

```php
// bank_soal
$table->index('paket_id');

// jadwal_tryout
$table->index('paket_id');

// peserta_jadwal (Hasil Ujian)
$table->index('user_id');
$table->index('jadwal_tryout_id');
```

---

## 5. Sistem Hak Akses (RBAC)

### A. Daftar Role & Permission

| Role           | Permission                         | Akses Menu                                                            |
|----------------|-------------------------------------|-----------------------------------------------------------------------|
| `super_admin`  | **Semua** (bypass via Gate)         | Semua menu tanpa batas                                                |
| `admin`        | `manage_soal`                       | Mapel, Paket Soal, Bank Soal, Stimulus, Paket Tryout, Jadwal Tryout   |
| `admin`        | `manage_peserta`                    | User Peserta, Sekolah, Kelas, Cetak Kartu                            |
| `admin`        | `manage_soal` + `manage_peserta`    | Semua menu di atas (gabungan)                                         |
| `peserta`      | —                                   | Portal Ujian saja                                                     |

### B. Seeder Role & Permission

```php
// database/seeders/RolePermissionSeeder.php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Buat Permissions
Permission::create(['name' => 'manage_soal']);
Permission::create(['name' => 'manage_peserta']);

// Buat Roles
$superAdmin = Role::create(['name' => 'super_admin']);
$admin      = Role::create(['name' => 'admin']);
$peserta    = Role::create(['name' => 'peserta']);

// Super Admin mendapat semua permission
$superAdmin->givePermissionTo(Permission::all());
```

### C. Model User (Update)

```php
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles, LogsActivity, SoftDeletes;

    // Relasi baru
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function kelases()  // Untuk Admin: kelas yang dikelola
    {
        return $this->belongsToMany(Kelas::class, 'admin_kelas');
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['username', 'email', 'role', 'sekolah_id', 'kelas_id'])
            ->logOnlyDirty();
    }
}
```

### D. Super Admin Gate (Bypass semua permission)

```php
// app/Providers/AuthServiceProvider.php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::before(function ($user, $ability) {
        return $user->hasRole('super_admin') ? true : null;
    });
}
```

---

## 6. Proteksi Resource Filament

### A. Resource Group: Soal

Diterapkan pada: `BankSoalResource`, `BankStimulusResource`, `RefMapelResource`, `RefPaketSoalResource`, `PaketTryoutResource`, `JadwalTryoutResource`.

```php
public static function canViewAny(): bool
{
    $user = auth()->user();
    return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_soal');
}
```

### B. Resource Group: Peserta

Diterapkan pada: `UserResource`, `KelasResource`, `SekolahResource`.

```php
public static function canViewAny(): bool
{
    $user = auth()->user();
    return $user->hasRole('super_admin') || $user->hasPermissionTo('manage_peserta');
}
```

### C. Data Scoping (School-Locked Admin)

Sistem menggunakan **School-Locked Scoping** di mana Admin dikunci pada `sekolah_id` tertentu:

- **UserResource (Peserta)**:
  - Query: `where('sekolah_id', auth()->user()->sekolah_id)`.
  - Form: `sekolah_id` otomatis terisi dan terkunci (disabled) untuk Admin.
  - Dropdown **Kelas** otomatis tersaring hanya untuk kelas di sekolah tersebut.

- **KelasResource**:
  - Query: `where('sekolah_id', auth()->user()->sekolah_id)`.
  - Form: Saat buat kelas, otomatis diasosiasikan ke `sekolah_id` si Admin.

---

## 7. Perubahan UI & UX

### A. Sticky Form Actions (Tablet Friendly)
Agar tombol **Save/Cancel** selalu terlihat di tablet, diterapkan CSS sticky melalui `renderHook` di `AdminPanelProvider`:
```php
.fi-form-actions {
    position: sticky !important;
    bottom: 0 !important;
    z-index: 10 !important;
    background-color: white !important;
    box-shadow: 0 -2px 6px rgba(0,0,0,0.05) !important;
}
```

### B. `UserAdminResource` - Pembuatan Admin dengan Relasi Sekolah
Sekarang menggunakan `Select` relasi ke model `Sekolah`, bukan text bebas, untuk memastikan konsistensi data.

```php
Forms\Components\Section::make('Hak Akses & Penugasan')
    ->description('Tentukan permission dan kelas yang dikelola admin ini')
    ->schema([
        Forms\Components\CheckboxList::make('permissions')
            ->label('Hak Akses')
            ->options([
                'manage_soal'    => '📝 Kelola Soal (Mapel, Paket, Bank Soal, Stimulus, Tryout)',
                'manage_peserta' => '👥 Kelola Peserta (User, Sekolah, Kelas, Kartu)',
            ])
            ->required()
            ->columns(1),

        Forms\Components\CheckboxList::make('kelases')
            ->label('Kelas yang Dikelola')
            ->relationship('kelases', 'nama_kelas')
            ->searchable()
            ->columns(2),
    ]),
```

### B. `UserResource` (Peserta) - Tambahan Field

```php
Forms\Components\Select::make('sekolah_id')
    ->label('Sekolah')
    ->relationship('sekolah', 'nama_sekolah')
    ->searchable()
    ->preload()
    ->required()
    ->live(),

Forms\Components\Select::make('kelas_id')
    ->label('Kelas')
    ->relationship('kelas', 'nama_kelas', fn (Builder $query, Forms\Get $get) =>
        $query->where('sekolah_id', $get('sekolah_id'))
    )
    ->searchable()
    ->preload()
    ->required()
    ->disabled(fn (Forms\Get $get) => !$get('sekolah_id')),

Forms\Components\TextInput::make('nomor_peserta')
    ->label('Nomor Peserta')
    ->disabled()
    ->helperText('Otomatis di-generate saat simpan'),
```

### C. Nomor Peserta Auto-Generator

```php
// Format: SMA01-10A-001
// Logic di Model atau Observer
public static function generateNomorPeserta(User $user): string
{
    $sekolah = $user->sekolah;
    $kelas   = $user->kelas;

    $prefix = strtoupper(substr($sekolah->nama_sekolah, 0, 5));
    $kelasCode = str_replace([' ', '-'], '', $kelas->nama_kelas);

    $lastNumber = User::where('kelas_id', $kelas->id)
        ->whereNotNull('nomor_peserta')
        ->count();

    return sprintf('%s-%s-%03d', $prefix, $kelasCode, $lastNumber + 1);
}
```

---

## 8. Audit Trail (Activity Log)

### Aktivitas yang Dicatat

| Aksi                     | Tabel yang Terpengaruh | Contoh Log                                 |
|--------------------------|------------------------|--------------------------------------------|
| Create Soal              | `bank_soal`            | `Admin A created question #52`             |
| Edit Soal                | `bank_soal`            | `Admin A updated question #52`             |
| Delete Soal              | `bank_soal`            | `Admin A deleted question #52`             |
| Create Peserta           | `users`                | `Admin B created participant #120`         |
| Edit Peserta             | `users`                | `Admin B updated participant #120`         |
| Delete Peserta           | `users`                | `Admin B deleted participant #120`         |

### Data yang Tersimpan di `activity_log`

| Field           | Keterangan                                 |
|-----------------|---------------------------------------------|
| `causer_id`     | ID Admin yang melakukan aksi                |
| `description`   | Deskripsi aksi (created, updated, deleted)  |
| `subject_id`    | ID record yang terpengaruh                  |
| `properties`    | Data lama & baru (JSON diff)                |

---

## 9. Keamanan

| Aspek             | Implementasi                                               |
|-------------------|------------------------------------------------------------|
| Password Hashing  | Default Laravel (`bcrypt` / `argon2`)                      |
| HTTPS             | Force di production (`APP_URL=https://...`)                |
| Rate Limiting     | `ThrottleRequests` pada route auth (maks 5x / menit)      |
| Soft Deletes      | `users`, `kelas`, `sekolah`, `bank_soal`, `paket_tryout`  |
| Backups           | Harian, retensi 7 hari (`schedule:run` via cron)           |

---

## 10. Dashboard Statistik (Filament Widgets)

Widget pada halaman utama admin panel:

| Widget              | Isi                                 |
|---------------------|-------------------------------------|
| Total Peserta       | Jumlah user dengan role `peserta`   |
| Total Kelas         | Jumlah kelas aktif                  |
| Total Sekolah       | Jumlah sekolah terdaftar            |
| Total Soal          | Jumlah bank soal                    |
| Total Paket Tryout  | Jumlah paket tryout aktif           |

---

## 11. Seeder Data Dummy (Testing)

```php
// database/seeders/DummyDataSeeder.php

// 10 Sekolah
// 30 Kelas (3 per sekolah)
// 300 Peserta (10 per kelas)
// 1000 Soal (bervariasi per mapel & paket)
// 5 Paket Tryout
```

---

## 12. Validasi Form Filament

| Field           | Validasi                        |
|-----------------|----------------------------------|
| `nama_kelas`    | `required`, `max:50`             |
| `npsn`          | `required`, `digits:8`, `unique` |
| `email`         | `required`, `email`, `unique`    |
| `username`      | `required`, `unique`             |
| `nama_sekolah`  | `required`, `max:100`            |

---

## 13. Langkah Eksekusi (Urutan)

| # | Langkah                                            | Perintah / File                                 |
|---|----------------------------------------------------|-------------------------------------------------|
| 1 | Install Packages                                   | `composer require spatie/laravel-permission ...` |
| 2 | Publish & Migrate                                  | `php artisan vendor:publish ...` + `migrate`     |
| 3 | Buat Enum (`UserRole`, `Jenjang`)                  | `app/Enums/*.php`                                |
| 4 | Buat Migration (sekolah, kelas, update users)      | `database/migrations/*.php`                      |
| 5 | Buat Model (Sekolah, Kelas) + Update User          | `app/Models/*.php`                               |
| 6 | Buat Seeder (RolePermission, DummyData)            | `database/seeders/*.php`                         |
| 7 | Buat/Update Filament Resources                     | `app/Filament/Resources/*.php`                   |
| 8 | Update AuthServiceProvider (Gate bypass)           | `app/Providers/AuthServiceProvider.php`          |
| 9 | Update Dashboard Widgets                           | `app/Filament/Widgets/*.php`                     |
| 10| Konfigurasi Backup Schedule                        | `app/Console/Kernel.php`                         |
| 11| Testing & Verifikasi                               | Manual testing via Filament                      |

---

## 14. Rencana Masa Depan

| Prioritas | Fitur                       | Keterangan                          |
|-----------|-----------------------------|--------------------------------------|
| Menengah  | Multi-tenant per sekolah    | Jika platform dipakai banyak pihak   |
| Menengah  | API ujian (mobile)          | REST API untuk aplikasi mobile       |
| Rendah    | Export hasil CSV/PDF        | Download hasil ujian per kelas       |

---

*Dokumentasi ini merupakan panduan utama untuk tahap eksekusi sistem CBT Bimbel Excellent.*
