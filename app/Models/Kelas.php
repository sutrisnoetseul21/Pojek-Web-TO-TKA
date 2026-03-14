<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Kelas extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'jenjang',
        'sekolah_id',
        'keterangan',
    ];

    /**
     * Relasi ke sekolah
     */
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    /**
     * Relasi ke peserta di kelas ini
     */
    public function peserta()
    {
        return $this->hasMany(User::class, 'kelas_id');
    }

    /**
     * Relasi ke admin yang mengelola kelas ini
     */
    public function admins()
    {
        return $this->belongsToMany(User::class, 'admin_kelas');
    }

    /**
     * Activity Log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_kelas', 'jenjang', 'sekolah_id', 'keterangan'])
            ->logOnlyDirty();
    }
}
