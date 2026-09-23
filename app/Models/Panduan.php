<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panduan extends Model
{
    use HasFactory;

    protected $table = 'panduans';

    protected $fillable = [
        'role',
        'judul',
        'icon',
        'link_gdrive',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan'    => 'integer',
    ];

    /**
     * Role default beserta icon pendukungnya.
     */
    public const ROLES_DEFAULT = [
        'Admin'          => 'bi bi-shield-check',
        'Guru'           => 'bi bi-person-badge',
        'Wali Kelas'     => 'bi bi-people',
        'Kepala Sekolah' => 'bi bi-building',
        'Siswa'          => 'bi bi-person',
        'Orang Tua'      => 'bi bi-heart',
    ];
}
