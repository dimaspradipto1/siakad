<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Daftar role yang tersedia.
     */
    public const ROLES = [
        'admin',
        'guru',
        'wali kelas',
        'kepala sekolah',
        'siswa',
        'orang tua',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'roles',
        'is_active',
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

    public function guru(): HasOneThrough
    {
        return $this->hasOneThrough(Guru::class, Pegawai::class, 'user_id', 'pegawai_id', 'id', 'id');
    }

    public function pengumuman()
    {
        return $this->hasMany(Pengumuman::class, 'user_id');
    }

    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'user_id');
    }

    public function pegawai()
    {
        return $this->hasOne(Pegawai::class, 'user_id');
    }

    public function orangTua()
    {
        return $this->hasOne(OrangTua::class, 'user_id');
    }

    /**
     * Cek apakah guru ini juga sedang ditugaskan sebagai wali kelas
     * pada tahun ajaran yang aktif.
     */
    public function isWaliKelasAktif(): bool
    {
        $guru = $this->pegawai?->guru ?? $this->guru;
        if (!$guru && $this->pegawai_id) {
            $guru = Guru::where('pegawai_id', $this->pegawai_id)->first();
        }
        if (!$guru) {
            $guru = Guru::where('user_id', $this->id)->first();
        }
        if (!$guru) {
            $pegawai = Pegawai::where('user_id', $this->id)->first();
            if (!$pegawai) {
                $nameBase = trim(explode(',', $this->name)[0]);
                $pegawai = Pegawai::where('nama_pegawai', 'like', '%' . $nameBase . '%')->first();
            }
            if ($pegawai) {
                $guru = Guru::where('pegawai_id', $pegawai->id)->first();
            }
        }

        if (!$guru) {
            $nameBase = trim(explode(',', $this->name)[0]);
            return WaliKelas::whereHas('guru.pegawai', function($q) use ($nameBase) {
                $q->where('nama_pegawai', 'like', '%' . $nameBase . '%');
            })->exists();
        }

        return $guru->waliKelas()->exists();
    }

    /**
     * Role yang sedang aktif dipakai untuk navigasi (bisa berbeda dari
     * roles asli jika guru sedang beralih ke tampilan Wali Kelas).
     */
    public function activeRole(): string
    {
        $active = session('active_role');

        if ($active && in_array($this->roles, ['guru', 'wali kelas']) && $this->isWaliKelasAktif()) {
            return $active;
        }

        return $this->roles ?? '';
    }

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
        ];
    }
}
