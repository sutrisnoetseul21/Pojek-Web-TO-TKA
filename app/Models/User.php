<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Enums\UserRole;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity, SoftDeletes;

    protected static function booted()
    {
        static::saved(function (User $user) {
            if ($user->role === 'admin' && $user->manage_all_kelas && $user->sekolah_id) {
                $kelasIds = \App\Models\Kelas::where('sekolah_id', $user->sekolah_id)->pluck('id');
                $user->kelases()->sync($kelasIds);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'role',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'sekolah',
        'jenis_kelamin',
        'is_biodata_complete',
        'plain_password',
        'jenjang',
        'npsn',
        'sekolah_id',
        'kelas_id',
        'nomor_peserta',
        'manage_all_kelas',
    ];

    /**
     * Fallback name for Filament if name is null
     */
    public function getNameAttribute($value): string
    {
        return $value ?? $this->username ?? 'User';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tanggal_lahir' => 'date',
            'is_biodata_complete' => 'boolean',
            'manage_all_kelas' => 'boolean',
        ];
    }

    /**
     * Check if user can access Filament panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPeserta(): bool
    {
        return $this->role === 'peserta';
    }

    // ─── Relasi Baru ──────────────────────────────────────────

    /**
     * Relasi ke Sekolah
     */
    public function sekolahRelation()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    /**
     * Relasi ke Kelas (untuk peserta)
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Relasi ke Kelas yang dikelola (untuk admin, many-to-many)
     */
    public function kelases()
    {
        return $this->belongsToMany(Kelas::class, 'admin_kelas');
    }

    // ─── Relasi yang Sudah Ada ────────────────────────────────

    /**
     * Relasi ke jadwal tryout yang diikuti
     */
    public function jadwalTryouts()
    {
        return $this->belongsToMany(JadwalTryout::class, 'peserta_jadwal')
            ->withPivot(['token_used', 'status', 'waktu_mulai', 'waktu_selesai', 'sisa_waktu', 'total_nilai'])
            ->withTimestamps();
    }

    // ─── Scopes ────────────────────────────────────────────────

    /**
     * Scope untuk peserta saja
     */
    public function scopePeserta($query)
    {
        return $query->where('role', 'peserta');
    }

    /**
     * Scope untuk super admin saja
     */
    public function scopeSuperAdmin($query)
    {
        return $query->where('role', 'super_admin');
    }

    /**
     * Scope untuk admin saja
     */
    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    // ─── Nomor Peserta Generator ──────────────────────────────

    /**
     * Generate nomor peserta: SEKOLAH-KELAS-URUT
     */
    public static function generateNomorPeserta(User $user): ?string
    {
        if (!$user->sekolah_id || !$user->kelas_id) {
            return null;
        }

        $sekolah = $user->sekolahRelation;
        $kelas = $user->kelas;

        if (!$sekolah || !$kelas) {
            return null;
        }

        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $sekolah->nama_sekolah), 0, 5));
        $kelasCode = str_replace([' ', '-'], '', $kelas->nama_kelas);

        $lastNumber = User::where('kelas_id', $kelas->id)
            ->whereNotNull('nomor_peserta')
            ->count();

        return sprintf('%s-%s-%03d', $prefix, $kelasCode, $lastNumber + 1);
    }

    // ─── Activity Log ─────────────────────────────────────────

    /**
     * Activity Log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['username', 'email', 'role', 'nama_lengkap', 'sekolah_id', 'kelas_id', 'nomor_peserta', 'manage_all_kelas'])
            ->logOnlyDirty();
    }
}
