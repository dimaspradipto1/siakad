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
        'pegawai',
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
        'username',
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
     * Mengambil daftar semua role yang dimiliki user dalam bentuk array lowercase.
     */
    public function getRolesList(): array
    {
        $raw = $this->attributes['roles'] ?? '';
        $list = [];

        if (is_array($raw)) {
            $list = $raw;
        } elseif (is_string($raw)) {
            $trimmed = trim($raw);
            if (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    $list = $decoded;
                }
            } else {
                $list = array_map('trim', explode(',', $trimmed));
            }
        }

        $list = array_map('strtolower', array_filter($list));

        // Otomatis sertakan role 'wali kelas' jika guru ini memiliki tugas wali kelas aktif
        if (in_array('guru', $list) && !in_array('wali kelas', $list) && $this->isWaliKelasAktif()) {
            $list[] = 'wali kelas';
        }

        return array_values(array_unique($list));
    }

    /**
     * Cek apakah user memiliki satu atau lebih role tertentu.
     */
    public function hasRole(string|array $checkRoles): bool
    {
        $userRoles = $this->getRolesList();
        $checkList = is_array($checkRoles) ? $checkRoles : array_map('trim', explode(',', $checkRoles));

        foreach ($checkList as $r) {
            if (in_array(strtolower($r), $userRoles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cek apakah user memiliki lebih dari 1 role akses.
     */
    public function hasMultiRole(): bool
    {
        return count($this->getRolesList()) > 1;
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
            $guru = Guru::whereHas('pegawai', function($q) {
                $q->where('user_id', $this->id);
            })->first();
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
     * Role yang sedang aktif dipakai pada sesi saat ini.
     */
    public function activeRole(): string
    {
        $rolesList = $this->getRolesList();
        $active = session('active_role');

        if ($active && in_array($active, $rolesList)) {
            return $active;
        }

        if (count($rolesList) === 1) {
            return $rolesList[0];
        }

        return '';
    }

    /**
     * Accessor untuk $user->roles untuk kompatibilitas kode yang langsung mengakses ->roles
     */
    public function getRolesAttribute($value): string
    {
        $active = session('active_role');
        $rolesList = $this->getRolesList();

        if ($active && in_array($active, $rolesList)) {
            return $active;
        }

        if (count($rolesList) === 1) {
            return $rolesList[0];
        }

        return $value ?? '';
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
