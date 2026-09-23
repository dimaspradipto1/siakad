<?php

namespace Database\Seeders;

use App\Models\Panduan;
use Illuminate\Database\Seeder;

class PanduanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPanduans = [
            [
                'role'        => 'Admin',
                'judul'       => 'Buku Panduan Admin',
                'icon'        => 'bi bi-shield-check',
                'link_gdrive' => 'https://drive.google.com/',
                'urutan'      => 1,
                'is_active'   => true,
            ],
            [
                'role'        => 'Guru',
                'judul'       => 'Buku Panduan Guru',
                'icon'        => 'bi bi-person-badge',
                'link_gdrive' => 'https://drive.google.com/',
                'urutan'      => 2,
                'is_active'   => true,
            ],
            [
                'role'        => 'Wali Kelas',
                'judul'       => 'Buku Panduan Wali Kelas',
                'icon'        => 'bi bi-people',
                'link_gdrive' => 'https://drive.google.com/',
                'urutan'      => 3,
                'is_active'   => true,
            ],
            [
                'role'        => 'Kepala Sekolah',
                'judul'       => 'Buku Panduan Kepala Sekolah',
                'icon'        => 'bi bi-building',
                'link_gdrive' => 'https://drive.google.com/',
                'urutan'      => 4,
                'is_active'   => true,
            ],
            [
                'role'        => 'Siswa',
                'judul'       => 'Buku Panduan Siswa',
                'icon'        => 'bi bi-person',
                'link_gdrive' => 'https://drive.google.com/',
                'urutan'      => 5,
                'is_active'   => true,
            ],
            [
                'role'        => 'Orang Tua',
                'judul'       => 'Buku Panduan Orang Tua',
                'icon'        => 'bi bi-heart',
                'link_gdrive' => 'https://drive.google.com/',
                'urutan'      => 6,
                'is_active'   => true,
            ],
        ];

        foreach ($defaultPanduans as $data) {
            Panduan::updateOrCreate(
                ['role' => $data['role']],
                $data
            );
        }
    }
}
