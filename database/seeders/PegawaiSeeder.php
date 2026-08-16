<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pegawais = [
            [
                'nip'           => '196805121990031005',
                'nama_pegawai'  => 'Yusal, S.Pd.',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir'  => 'Batam',
                'tgl_lahir'     => '1968-05-12',
                'jabatan'       => 'Kepala Sekolah',
                'agama'         => 'Islam',
                'nomor_wa'      => '081270123456',
                'alamat'        => 'Perumahan Tiban Indah Permai Blok A No. 12, Sekupang, Batam',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nip'           => '197211151996021003',
                'nama_pegawai'  => 'Rusli, S.Pd.SD.',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir'  => 'Tanjungpinang',
                'tgl_lahir'     => '1972-11-15',
                'jabatan'       => 'Guru Kelas',
                'agama'         => 'Islam',
                'nomor_wa'      => '081372345678',
                'alamat'        => 'Kavling Tiban Koperasi Blok C No. 4, Sekupang, Batam',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nip'           => '197509202005022002',
                'nama_pegawai'  => 'Indarita Kusmarlina, S.Pd.',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir'  => 'Tanjungpinang',
                'tgl_lahir'     => '1975-09-20',
                'jabatan'       => 'Guru Kelas',
                'agama'         => 'Islam',
                'nomor_wa'      => '081177654321',
                'alamat'        => 'Batu Aji Permai Blok H No. 15, Batu Aji, Batam',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nip'           => '198004182008011012',
                'nama_pegawai'  => 'Budi Santoso, S.Pd.',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir'  => 'Batam',
                'tgl_lahir'     => '1980-04-18',
                'jabatan'       => 'Guru Kelas',
                'agama'         => 'Islam',
                'nomor_wa'      => '081277889900',
                'alamat'        => 'Tiban McDermott Blok Q No. 9, Sekupang, Batam',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nip'           => '198808242018021001',
                'nama_pegawai'  => 'Ali Rusdin',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir'  => 'Batam',
                'tgl_lahir'     => '1988-08-24',
                'jabatan'       => 'Operator Sekolah',
                'agama'         => 'Islam',
                'nomor_wa'      => '085265432109',
                'alamat'        => 'Perumahan Tiban Mas Blok D No. 22, Sekupang, Batam',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nip'           => '198505122010122005',
                'nama_pegawai'  => 'Siti Aminah, S.Pd.I',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir'  => 'Medan',
                'tgl_lahir'     => '1985-05-12',
                'jabatan'       => 'Guru Agama Islam',
                'agama'         => 'Islam',
                'nomor_wa'      => '081364556677',
                'alamat'        => 'Tiban Riau Bertuah Blok G No. 1, Sekupang, Batam',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nip'           => '199109022015041003',
                'nama_pegawai'  => 'Eko Prasetyo, S.Pd.',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir'  => 'Batam',
                'tgl_lahir'     => '1991-09-02',
                'jabatan'       => 'Guru Penjasorkes',
                'agama'         => 'Islam',
                'nomor_wa'      => '089623456789',
                'alamat'        => 'Tiban Kampung RT 02/RW 01, Sekupang, Batam',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nip'           => '199402142020082001',
                'nama_pegawai'  => 'Dewi Lestari, A.Md.',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir'  => 'Batam',
                'tgl_lahir'     => '1994-02-14',
                'jabatan'       => 'Staf Tata Usaha',
                'agama'         => 'Islam',
                'nomor_wa'      => '087798765432',
                'alamat'        => 'Perumahan Tiban Lestari Blok F No. 8, Sekupang, Batam',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ];

        foreach ($pegawais as $pegawaiData) {
            $user = User::query()->where('name', $pegawaiData['nama_pegawai'])->first();
            if (!$user) {
                $role = 'pegawai';
                if (stripos($pegawaiData['jabatan'], 'Kepala Sekolah') !== false) {
                    $role = 'kepala sekolah';
                } elseif (stripos($pegawaiData['jabatan'], 'Wali Kelas') !== false) {
                    $role = 'wali kelas';
                } elseif (stripos($pegawaiData['jabatan'], 'Guru') !== false) {
                    $role = 'guru';
                }

                $username = preg_replace('/[^A-Za-z0-9]/', '', strtolower($pegawaiData['nip']));
                $user = User::create([
                    'name' => $pegawaiData['nama_pegawai'],
                    'username' => $username,
                    'email' => $username . '@gmail.com',
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'roles' => $role,
                    'is_active' => true,
                ]);
            }
            $pegawaiData['user_id'] = $user->id;

            Pegawai::updateOrCreate(
                ['nip' => $pegawaiData['nip']],
                $pegawaiData
            );
        }
    }
}
