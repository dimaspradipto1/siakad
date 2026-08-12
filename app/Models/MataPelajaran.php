<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajarans';

    protected $fillable = [
        'kode_mapel',
        'nama_mata_pelajaran',
        'kkm',
        'tp_optimal',
        'tp_peningkatan',
        'kelas_id',
        'tahun_ajaran_id',
        'semester_id',
        'guru_id',
        'hari_mengajar',
        'jam_mengajar',
        'status',
        'is_locked_harian',
        'is_locked_mid',
        'is_locked_pas',
        'is_locked_raport',
    ];

    protected $casts = [
        'tp_optimal'       => 'array',
        'tp_peningkatan'   => 'array',
        'is_locked_harian' => 'boolean',
        'is_locked_mid'    => 'boolean',
        'is_locked_pas'    => 'boolean',
        'is_locked_raport' => 'boolean',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function kehadiran(): HasMany
    {
        return $this->hasMany(Kehadiran::class, 'mata_pelajaran_id');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class, 'mata_pelajaran_id');
    }
}
