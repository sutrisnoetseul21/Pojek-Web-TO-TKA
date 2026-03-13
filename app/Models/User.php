<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
    ];

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

    /**
     * Relasi ke jadwal tryout yang diikuti
     */
    public function jadwalTryouts()
    {
        return $this->belongsToMany(JadwalTryout::class, 'peserta_jadwal')
            ->withPivot(['token_used', 'status', 'waktu_mulai', 'waktu_selesai', 'sisa_waktu', 'total_nilai'])
            ->withTimestamps();
    }

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
}
