<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Sekolah extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'sekolah';

    protected $fillable = [
        'nama_sekolah',
        'npsn',
        'jenjang',
        'alamat',
    ];

    /**
     * Relasi ke kelas-kelas di sekolah ini
     */
    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }

    /**
     * Relasi ke user-user di sekolah ini
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relasi ke admin-admin di sekolah ini
     */
    public function admins()
    {
        return $this->hasMany(User::class)->where('role', 'admin');
    }

    /**
     * Relasi ke peserta-peserta di sekolah ini
     */
    public function peserta()
    {
        return $this->hasMany(User::class)->where('role', 'peserta');
    }

    /**
     * Activity Log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_sekolah', 'npsn', 'jenjang', 'alamat'])
            ->logOnlyDirty();
    }
}
