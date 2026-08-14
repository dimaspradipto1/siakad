<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\OrangTua;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SiswaImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        if (empty($row['nisn']) || empty($row['nama_lengkap'])) {
            return null;
        }

        // 1. Process Parent Data
        $orangTuaId = null;
        $namaAyah = $row['nama_ayah'] ?? null;
        $namaIbu = $row['nama_ibu'] ?? null;
        $emailOrtu = $row['email_orang_tua'] ?? null;

        if ($namaAyah || $namaIbu || $emailOrtu) {
            $userOrtu = null;
            if ($emailOrtu) {
                $usernameOrtu = 'ortu_' . preg_replace('/[^A-Za-z0-9]/', '', strtolower($row['nisn']));
                $userOrtu = \App\Models\User::where('email', $emailOrtu)->first();
                if (!$userOrtu) {
                    $userOrtu = \App\Models\User::create([
                        'name' => $namaAyah ?: ($namaIbu ?: 'Orang Tua'),
                        'username' => $usernameOrtu,
                        'email' => $emailOrtu,
                        'password' => \Illuminate\Support\Facades\Hash::make('password'),
                        'roles' => 'orang tua',
                        'is_active' => true,
                    ]);
                }
            }

            // Find existing parent or create new
            $orangTua = null;
            if ($emailOrtu) {
                $orangTua = OrangTua::where('email', $emailOrtu)->first();
            }
            if (!$orangTua && $namaIbu) {
                $orangTua = OrangTua::where('nama_ibu', $namaIbu)->first();
            }
            
            if (!$orangTua) {
                $orangTua = OrangTua::create([
                    'user_id' => $userOrtu ? $userOrtu->id : null,
                    'nama_ayah' => $namaAyah,
                    'pekerjaan_ayah' => $row['pekerjaan_ayah'] ?? null,
                    'nomor_wa' => $row['no_whatsapp_ayah'] ?? null,
                    'nama_ibu' => $namaIbu,
                    'pekerjaan_ibu' => $row['pekerjaan_ibu'] ?? null,
                    'nomor_wa_ibu' => $row['no_whatsapp_ibu'] ?? null,
                    'alamat' => $row['alamat_orang_tua'] ?? null,
                    'email' => $emailOrtu,
                ]);
            } else {
                $orangTua->update([
                    'nama_ayah' => $namaAyah ?: $orangTua->nama_ayah,
                    'pekerjaan_ayah' => $row['pekerjaan_ayah'] ?? $orangTua->pekerjaan_ayah,
                    'nomor_wa' => $row['no_whatsapp_ayah'] ?? $orangTua->nomor_wa,
                    'nama_ibu' => $namaIbu ?: $orangTua->nama_ibu,
                    'pekerjaan_ibu' => $row['pekerjaan_ibu'] ?? $orangTua->pekerjaan_ibu,
                    'nomor_wa_ibu' => $row['no_whatsapp_ibu'] ?? $orangTua->nomor_wa_ibu,
                    'alamat' => $row['alamat_orang_tua'] ?? $orangTua->alamat,
                    'email' => $emailOrtu ?: $orangTua->email,
                ]);
            }
            $orangTuaId = $orangTua->id;
        }

        // 2. Resolve Kelas ID (nullable until Pembagian Kelas is assigned)
        $kelasId = null;

        // 3. Process Student User Account
        $nisn = $row['nisn'];
        $usernameSiswa = preg_replace('/[^A-Za-z0-9]/', '', strtolower($nisn));
        $emailSiswa = $row['email_siswa'] ?? ($usernameSiswa . '@gmail.com');
        $userSiswa = \App\Models\User::where('username', $usernameSiswa)->orWhere('email', $emailSiswa)->first();
        if (!$userSiswa) {
            $userSiswa = \App\Models\User::create([
                'name' => $row['nama_lengkap'],
                'username' => $usernameSiswa,
                'email' => $emailSiswa,
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'roles' => 'siswa',
                'is_active' => true,
            ]);
        }

        // 4. Safe Date Parsing helper
        $tglLahir = null;
        if (!empty($row['tanggal_lahir'])) {
            try {
                if (is_numeric($row['tanggal_lahir'])) {
                    $tglLahir = \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal_lahir']))->format('Y-m-d');
                } else {
                    $tglLahir = \Carbon\Carbon::parse($row['tanggal_lahir'])->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $tglLahir = date('Y-m-d');
            }
        } else {
            $tglLahir = date('Y-m-d');
        }

        $tglMasuk = null;
        if (!empty($row['tanggal_masuk'])) {
            try {
                if (is_numeric($row['tanggal_masuk'])) {
                    $tglMasuk = \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal_masuk']))->format('Y-m-d');
                } else {
                    $tglMasuk = \Carbon\Carbon::parse($row['tanggal_masuk'])->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $tglMasuk = date('Y-m-d');
            }
        } else {
            $tglMasuk = date('Y-m-d');
        }

        // 5. Save/Update Siswa
        return Siswa::updateOrCreate(
            ['nisn' => $nisn],
            [
                'user_id' => $userSiswa->id,
                'orang_tua_id' => $orangTuaId,
                'kelas_id' => $kelasId,
                'nama_siswa' => $row['nama_lengkap'],
                'jenis_kelamin' => $row['jenis_kelamin'] ?? 'Laki-laki',
                'tempat_lahir' => $row['tempat_lahir'] ?? '',
                'tgl_lahir' => $tglLahir,
                'agama' => $row['agama'] ?? 'Islam',
                'nomor_wa' => $row['no_whatsapp'] ?? null,
                'alamat' => $row['alamat'] ?? null,
                'tgl_masuk' => $tglMasuk,
                'status' => $row['status'] ?? 'Aktif',
            ]
        );
    }

    public function rules(): array
    {
        return [
            'nisn'       => 'required',
            'nama_lengkap' => 'required',
        ];
    }
}
