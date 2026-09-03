<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\MataPelajaranAktifController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\WaliKelasController;
use App\Http\Controllers\OrangTuaController;
use App\Http\Controllers\EkstrakurikulerController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\MataPelajaranController;
use App\Http\Controllers\JenisKehadiranController;
use App\Http\Controllers\KehadiranController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\JenisCatatanController;
use App\Http\Controllers\CatatanSiswaController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\ProfilSekolahController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\PembagianKelasController;
use App\Http\Controllers\MateriPembelajaranController;

/*
|--------------------------------------------------------------------------
| Web Routes - SIAKAD SD Negeri 007 Sekupang
|--------------------------------------------------------------------------
*/


Route::controller(AuthController::class)->group(function () {
    Route::get('/', 'index')->name('login');
    Route::post('/login', 'loginProses')->name('loginProses');
    Route::post('/logout', 'logout')->name('logout');
});


Route::middleware(['auth', 'checkrole'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/orang-tua/pilih-anak/{id}', [DashboardController::class, 'selectChild'])->name('orangtua.select-child');
    Route::post('switch-role/{role}', [AuthController::class, 'switchRole'])->name('switch-role');
    Route::resource('user', UserController::class);
    Route::get('pegawai/export', [PegawaiController::class, 'export'])->name('pegawai.export');
    Route::post('pegawai/import', [PegawaiController::class, 'import'])->name('pegawai.import');
    Route::get('pegawai/template', [PegawaiController::class, 'template'])->name('pegawai.template');
    Route::resource('pegawai', PegawaiController::class);
    Route::get('guru/export', [GuruController::class, 'export'])->name('guru.export');
    Route::post('guru/import', [GuruController::class, 'import'])->name('guru.import');
    Route::get('guru/template', [GuruController::class, 'template'])->name('guru.template');
    Route::resource('guru', GuruController::class);
    Route::get('matapelajaranaktif/export', [MataPelajaranAktifController::class, 'export'])->name('matapelajaranaktif.export');
    Route::post('matapelajaranaktif/import', [MataPelajaranAktifController::class, 'import'])->name('matapelajaranaktif.import');
    Route::get('matapelajaranaktif/template', [MataPelajaranAktifController::class, 'template'])->name('matapelajaranaktif.template');
    Route::resource('matapelajaranaktif', MataPelajaranAktifController::class);
    Route::resource('tahun-ajaran', TahunAjaranController::class);
    Route::get('api/semesters-by-ta/{tahun_ajaran_id}', function ($tahun_ajaran_id) {
        $semesters = \App\Models\Semester::query()->where('tahun_ajaran_id', $tahun_ajaran_id)->orderBy('id')->get();
        if ($semesters->isEmpty()) {
            $allSemesters = \App\Models\Semester::query()->get();
            if ($allSemesters->isNotEmpty()) {
                $semesters = $allSemesters->unique('nama_semester')->values();
            } else {
                $semesters = collect([
                    (object)['id' => 1, 'nama_semester' => 'Semester 1 (Ganjil)'],
                    (object)['id' => 2, 'nama_semester' => 'Semester 2 (Genap)']
                ]);
            }
        }
        return response()->json($semesters);
    })->name('api.semesters-by-ta');
    Route::resource('semester', SemesterController::class);
    Route::resource('kelas', KelasController::class);
    Route::resource('walikelas', WaliKelasController::class);
    Route::get('orang-tua/export', [OrangTuaController::class, 'export'])->name('orang-tua.export');
    Route::post('orang-tua/import', [OrangTuaController::class, 'import'])->name('orang-tua.import');
    Route::get('orang-tua/template', [OrangTuaController::class, 'template'])->name('orang-tua.template');
    Route::resource('orang-tua', OrangTuaController::class);
    Route::get('matapelajaran/jadwal', [MataPelajaranController::class, 'jadwal'])->name('matapelajaran.jadwal');
    Route::get('ekstrakurikuler/rekap', [EkstrakurikulerController::class, 'rekapEkskul'])->name('ekstrakurikuler.rekap');
    Route::get('ekstrakurikuler-siswa', [EkstrakurikulerController::class, 'ekskulSiswa'])->name('ekstrakurikuler.siswa');
    Route::post('ekstrakurikuler-siswa/save', [EkstrakurikulerController::class, 'ekskulSiswaSave'])->name('ekstrakurikuler.siswa.save');
    Route::resource('ekstrakurikuler', EkstrakurikulerController::class);
    Route::get('siswa/export', [SiswaController::class, 'export'])->name('siswa.export');
    Route::post('siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
    Route::get('siswa/template', [SiswaController::class, 'template'])->name('siswa.template');
    Route::resource('siswa', SiswaController::class);
    Route::get('matapelajaran/export', [MataPelajaranController::class, 'export'])->name('matapelajaran.export');
    Route::post('matapelajaran/import', [MataPelajaranController::class, 'import'])->name('matapelajaran.import');
    Route::get('matapelajaran/template', [MataPelajaranController::class, 'template'])->name('matapelajaran.template');
    Route::resource('matapelajaran', MataPelajaranController::class);
    Route::resource('jeniskehadiran', JenisKehadiranController::class);
    Route::get('kehadiran/get-kelas-mapel', [KehadiranController::class, 'getKelasDanMapel'])->name('kehadiran.get-kelas-mapel');
    Route::post('kehadiran/save', [KehadiranController::class, 'bulkSave'])->name('kehadiran.save');
    Route::get('kehadiran/rekap', [KehadiranController::class, 'rekapKehadiran'])->name('kehadiran.rekap');
    Route::get('kehadiran/rekap/get-mapel', [KehadiranController::class, 'rekapGetMapel'])->name('kehadiran.rekap.get-mapel');
    Route::get('kehadiran/rekap/print', [KehadiranController::class, 'rekapKehadiranPrint'])->name('kehadiran.rekap.print');
    Route::get('kehadiran/rekap/catatan', [KehadiranController::class, 'rekapKehadiranCatatan'])->name('kehadiran.rekap.catatan');
    Route::resource('kehadiran', KehadiranController::class);
    Route::get('nilai/export', [NilaiController::class, 'export'])->name('nilai.export');
    Route::post('nilai/import', [NilaiController::class, 'import'])->name('nilai.import');
    Route::get('nilai/template', [NilaiController::class, 'template'])->name('nilai.template');
    
    // Custom Nilai Harian, MID, PAS, Raport and Rekap routes
    Route::get('nilai/harian', [NilaiController::class, 'harian'])->name('nilai.harian');
    Route::post('nilai/harian/get', [NilaiController::class, 'harianGet'])->name('nilai.harian.get');
    Route::post('nilai/harian/save', [NilaiController::class, 'harianSave'])->name('nilai.harian.save');

    Route::get('nilai/mid', [NilaiController::class, 'mid'])->name('nilai.mid');
    Route::post('nilai/mid/get', [NilaiController::class, 'midGet'])->name('nilai.mid.get');
    Route::post('nilai/mid/save', [NilaiController::class, 'midSave'])->name('nilai.mid.save');

    Route::get('nilai/pas', [NilaiController::class, 'pas'])->name('nilai.pas');
    Route::post('nilai/pas/get', [NilaiController::class, 'pasGet'])->name('nilai.pas.get');
    Route::post('nilai/pas/save', [NilaiController::class, 'pasSave'])->name('nilai.pas.save');

    Route::get('nilai/raport-input', [NilaiController::class, 'raportInput'])->name('nilai.raport-input');
    Route::post('nilai/raport-input/get', [NilaiController::class, 'raportInputGet'])->name('nilai.raport-input.get');
    Route::post('nilai/raport-input/save', [NilaiController::class, 'raportInputSave'])->name('nilai.raport-input.save');

    Route::get('nilai/rekap-mapel', [NilaiController::class, 'rekapMapel'])->name('nilai.rekap-mapel');
    Route::post('nilai/rekap-mapel/get', [NilaiController::class, 'rekapMapelGet'])->name('nilai.rekap-mapel.get');
    Route::get('nilai/rekap-mapel/export', [NilaiController::class, 'rekapMapelExport'])->name('nilai.rekap-mapel.export');
    Route::get('nilai/rekap-raport', [NilaiController::class, 'rekapRaport'])->name('nilai.rekap-raport');
    Route::post('nilai/rekap-raport/get', [NilaiController::class, 'rekapRaportGet'])->name('nilai.rekap-raport.get');
    Route::get('nilai/rekap-raport/export', [NilaiController::class, 'rekapRaportExport'])->name('nilai.rekap-raport.export');
    Route::get('nilai/cetak-raport', [NilaiController::class, 'cetakRaportList'])->name('nilai.cetak-raport');
    Route::get('nilai/cetak-raport/print/{siswa_id}/{tahun_ajaran_id}/{semester_id}', [NilaiController::class, 'cetakRaportPrint'])->name('nilai.cetak-raport.print');

    // Penguncian Nilai (admin only)
    Route::post('nilai/lock/toggle', [NilaiController::class, 'lockToggle'])->name('nilai.lock.toggle');

    Route::get('nilai/get-kelas-mapel', [NilaiController::class, 'getKelasDanMapel'])->name('nilai.get-kelas-mapel');
    Route::get('nilai/raport/personal', [NilaiController::class, 'cetakRaportPersonal'])->name('nilai.raport.personal');
    Route::get('kehadiran/personal', [KehadiranController::class, 'rekapKehadiranPersonal'])->name('kehadiran.personal');
    Route::get('ekstrakurikuler-personal', [EkstrakurikulerController::class, 'rekapEkskulPersonal'])->name('ekstrakurikuler.personal');
    Route::get('siswa-profile', [SiswaController::class, 'profile'])->name('siswa.profile');
    Route::post('siswa-profile/update', [SiswaController::class, 'profileUpdate'])->name('siswa.profile.update');
    Route::resource('nilai', NilaiController::class);
    Route::resource('jeniscatatan', JenisCatatanController::class);
    Route::resource('catatansiswa', CatatanSiswaController::class);
    Route::resource('pengumuman', PengumumanController::class);
    Route::resource('profil-sekolah', ProfilSekolahController::class);
    Route::resource('jabatan', JabatanController::class);
    Route::get('pembagiankelas/get-siswa', [PembagianKelasController::class, 'getSiswaByTahunAjaran'])->name('pembagiankelas.get-siswa');
    Route::resource('pembagiankelas', PembagianKelasController::class);
    Route::get('materipembelajaran/{id}/download', [MateriPembelajaranController::class, 'download'])->name('materipembelajaran.download');
    Route::resource('materipembelajaran', MateriPembelajaranController::class);
});
