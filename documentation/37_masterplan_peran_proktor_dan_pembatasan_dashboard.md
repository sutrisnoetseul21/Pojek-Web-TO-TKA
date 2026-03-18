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

---

## ✅ Rencana Verifikasi (Review)

1. **Tes Login Super Admin**: Memastikan semua menu (Bank Soal, User, Sekolah, Monitoring) tetap muncul lengkap tanpa eror.
2. **Tes Login Proktor**: Memastikan sidebar **HANYA** menampilkan grup `Monitoring Ujian` dan `Dashboard` dasar. Menu Bank Soal dan Setting harus hilang total.
3. **Tes Eksekusi**: Memastikan Proktor tetap bisa klik `Reset Login` dan data laporannya tersimpan di tabel log aktivitas dengan record username milik pribadi mereka.

---

> [!NOTE]
> Pemisahan ini membutuhkan pembuatan data Role di database. Jika bapak menyetujui rencana diagram di atas, kita bisa siapkan script command php artisan untuk generate role otomatisnya.
