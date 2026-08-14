<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswas';

    protected $fillable = [
        'user_id',
        'orang_tua_id',
        'kelas_id',
        'ekstrakurikuler_id',
        'nisn',
        'nama_siswa',
        'jenis_kelamin',
        'tempat_lahir',
        'tgl_lahir',
        'agama',
        'nomor_wa',
        'alamat',
        'tgl_masuk',
        'status',
        'foto',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orangTua(): BelongsTo
    {
        return $this->belongsTo(OrangTua::class, 'orang_tua_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($siswa) {
            if (!$siswa->user_id) {
                $username = preg_replace('/[^A-Za-z0-9]/', '', strtolower($siswa->nisn));
                $user = User::create([
                    'name' => $siswa->nama_siswa,
                    'username' => $username,
                    'email' => $username . '@gmail.com',
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'roles' => 'siswa',
                    'is_active' => true,
                ]);
                $siswa->user_id = $user->id;
                $siswa->saveQuietly();
            }
        });
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function ekstrakurikuler(): BelongsTo
    {
        return $this->belongsTo(Ekstrakurikuler::class, 'ekstrakurikuler_id');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class, 'siswa_id');
    }

    public function kehadiran(): HasMany
    {
        return $this->hasMany(Kehadiran::class, 'siswa_id');
    }

    public function catatanSiswa(): HasMany
    {
        return $this->hasMany(CatatanSiswa::class, 'siswa_id');
    }

    public function pembagianKelas(): HasMany
    {
        return $this->hasMany(PembagianKelas::class, 'siswa_id');
    }

    public function getKelasAktifAttribute()
    {
        $pk = $this->pembagianKelas()
            ->whereHas('tahunAjaran', function($q) {
                $q->where('status', 'Aktif');
            })
            ->latest('id')
            ->first();

        if ($pk && $pk->kelas) {
            return $pk->kelas;
        }

        return $this->kelas;
    }
}
