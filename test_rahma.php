<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== ALL USERS WITH ROL / NAME ===\n";
foreach (App\Models\User::all() as $u) {
    echo "ID: {$u->id} | Name: '{$u->name}' | Username: '{$u->username}' | Roles: '{$u->roles}' | Email: '{$u->email}' | PegawaiID: '{$u->pegawai_id}'\n";
}

echo "\n=== ALL PEGAWAI ===\n";
foreach (App\Models\Pegawai::all() as $p) {
    echo "ID: {$p->id} | Name: '{$p->nama_pegawai}' | UserID: '{$p->user_id}' | NIP: '{$p->nip}'\n";
}

echo "\n=== ALL GURU ===\n";
foreach (App\Models\Guru::all() as $g) {
    echo "ID: {$g->id} | UserID: '{$g->user_id}' | PegawaiID: '{$g->pegawai_id}'\n";
}

echo "\n=== ALL WALI KELAS ===\n";
foreach (App\Models\WaliKelas::all() as $w) {
    $guru = App\Models\Guru::find($w->guru_id);
    $pegawai = $guru ? App\Models\Pegawai::find($guru->pegawai_id) : null;
    $kelas = App\Models\Kelas::find($w->kelas_id);
    echo "WaliID: {$w->id} | GuruID: {$w->guru_id} | PegawaiName: '" . ($pegawai->nama_pegawai ?? 'N/A') . "' | KelasID: {$w->kelas_id} (" . ($kelas->nama_kelas ?? 'N/A') . ") | TA: {$w->tahun_ajaran_id}\n";
}
