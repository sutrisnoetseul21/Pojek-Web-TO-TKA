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
     * Activity Log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_sekolah', 'npsn', 'alamat'])
            ->logOnlyDirty();
    }
}
