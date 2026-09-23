<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       $this->call([
           UserSeeder::class,
           PegawaiSeeder::class,
           GuruSeeder::class,
           TahunAjaranSeeder::class,
           ProfilSekolahSeeder::class,
           SemesterSeeder::class,
           KelasSeeder::class,
           WaliKelasSeeder::class,
           OrangTuaSeeder::class,
           EkstrakurikulerSeeder::class,
           SiswaSeeder::class,
           PembagianKelasSeeder::class,
           MataPelajaranSeeder::class,
           JenisKehadiranSeeder::class,
           KehadiranSeeder::class,
           NilaiSeeder::class,
           JenisCatatanSeeder::class,
           CatatanSiswaSeeder::class,
           PengumumanSeeder::class,
           PanduanSeeder::class,
       ]);
    }
}
