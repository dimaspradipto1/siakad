@extends('layouts.dashboard.template')

@section('title', 'Dashboard')

@section('content')
    @unless ($hideNav ?? false)
        <div class="pagetitle">
            <h1>Dashboard</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>
    @endunless

    {{-- Flash success --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <section class="section dashboard">
        @if ($isDualRoleSelection ?? false)
            <div class="row justify-content-center align-items-center" style="min-height: 75vh;">
                <div class="col-lg-8 col-md-10">
                    <div class="card border-0 shadow-lg mb-4" style="border-radius: 16px;">
                        <div class="card-body p-4 p-md-5">
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                                <div>
                                    <h4 class="text-dark fw-bold mb-1">Selamat Datang, {{ $user->name }}</h4>
                                    <p class="text-muted small mb-0">Anda memiliki 2 peran. Silakan pilih mode login Anda:</p>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm px-3 rounded-pill fw-semibold">
                                        <i class="bi bi-box-arrow-right me-1"></i> Keluar
                                    </button>
                                </form>
                            </div>
                            
                            <div class="row g-4 pt-2">
                                <!-- Option 1: Guru Pengajar -->
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 shadow-sm text-center p-4" style="border-radius: 14px; background-color: #f8f9fa;">
                                        <div class="mb-3">
                                            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle shadow-sm" style="width: 64px; height: 64px; font-size: 1.8rem;">
                                                <i class="bi bi-person-video3"></i>
                                            </div>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-2">Guru Pengajar</h5>
                                        <p class="text-muted small mb-4">Input Nilai Harian, MID, PAS, Kehadiran, & Materi Pembelajaran.</p>
                                        <form method="POST" action="{{ route('switch-role', 'guru') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="border-radius: 10px;">
                                                Masuk sebagai Guru
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Option 2: Wali Kelas -->
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 shadow-sm text-center p-4" style="border-radius: 14px; background-color: #f8f9fa;">
                                        <div class="mb-3">
                                            <div class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded-circle shadow-sm" style="width: 64px; height: 64px; font-size: 1.8rem;">
                                                <i class="bi bi-easel"></i>
                                            </div>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-2">Wali Kelas ({{ $kelasWaliNama ?? 'Perwalian' }})</h5>
                                        <p class="text-muted small mb-4">Rekap Nilai Raport, Cetak Raport, Rekap Kehadiran, & Catatan Siswa.</p>
                                        <form method="POST" action="{{ route('switch-role', 'wali-kelas') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-dark w-100 py-2 fw-bold" style="border-radius: 10px; background-color: #212529;">
                                                Masuk sebagai Wali Kelas
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif ($activeRole === 'orang tua')
            <div class="row justify-content-center align-items-center" style="min-height: 75vh;">
                <div class="col-lg-10 col-xl-9">
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; background: #ffffff;">
                        <div class="card-body p-4 p-md-5">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <h4 class="text-dark fw-bold mb-1" style="font-size: 1.35rem;">Selamat Datang Bapak/Ibu</h4>
                                    <p class="text-muted small mb-0" style="font-size: 0.9rem;">Silakan pilih data anak yang ingin Anda lihat:</p>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm px-3 rounded-pill fw-semibold d-flex align-items-center gap-1" style="font-size: 0.85rem; border-color: #f87171; color: #ef4444;">
                                        <i class="bi bi-box-arrow-right"></i> Keluar
                                    </button>
                                </form>
                            </div>

                            <div class="row g-4 pt-2">
                                @if(isset($children) && $children->count() > 0)
                                    @foreach($children as $child)
                                        @php
                                            $activeTa = \App\Models\TahunAjaran::where('status', 'Aktif')->first();
                                            $pk = \App\Models\PembagianKelas::where('siswa_id', $child->id)->where('tahun_ajaran_id', $activeTa?->id)->first();
                                            if (!$pk) {
                                                $pk = \App\Models\PembagianKelas::where('siswa_id', $child->id)->latest('id')->first();
                                            }
                                            $kNama = $pk?->kelas?->nama_kelas ?? ($child->kelas?->nama_kelas ?? '-');
                                        @endphp
                                        <div class="col-md-{{ $children->count() === 1 ? '12' : ($children->count() === 2 ? '6' : '4') }}">
                                            <div class="card h-100 border-0 shadow-none text-center p-4" style="border-radius: 14px; background-color: #f8f9fa;">
                                                <div class="mb-3">
                                                    <div class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded-circle shadow-sm" style="width: 58px; height: 58px; font-size: 1.6rem; background-color: #1e2125 !important;">
                                                        <i class="bi bi-person-fill"></i>
                                                    </div>
                                                </div>
                                                <h5 class="fw-bold text-dark mb-1" style="font-size: 1.05rem;">{{ $child->nama_siswa }}</h5>
                                                <div class="mb-2">
                                                    <span class="badge bg-white text-secondary border fw-semibold px-2 py-1" style="font-size: 0.75rem; border-radius: 6px;">
                                                        Kelas {{ $kNama }}
                                                    </span>
                                                </div>
                                                <p class="text-muted small mb-4" style="font-size: 0.85rem;">NISN. {{ $child->nisn }}</p>

                                                <a href="{{ route('orangtua.select-child', ['id' => $child->id, 'redirect' => route('siswa.profile')]) }}" class="btn btn-dark w-100 py-2 fw-semibold" style="border-radius: 8px; background-color: #1e2125; border-color: #1e2125; font-size: 0.9rem;">
                                                    Lihat Data
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12 text-center py-4">
                                        <p class="text-muted">Data anak tidak ditemukan.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif ($activeRole === 'kepala sekolah')
            <div class="row">
                <!-- Selamat Datang -->
                <div class="col-12 mb-4">
                    <div class="card border-0 bg-light shadow-sm" style="border-radius: 12px;">
                        <div class="card-body p-4">
                            <h4 class="text-dark fw-bold mb-1">Selamat Datang, Kepala Sekolah</h4>
                            <p class="text-secondary mb-0" style="font-size: 0.95rem;">Berikut adalah ringkasan data sekolah anda:</p>
                        </div>
                    </div>
                </div>

                <!-- Row 1: 3 Columns -->
                <!-- Card 1: Jumlah Pegawai -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                        <div class="card-body pt-4 text-center d-flex flex-column justify-content-between">
                            <div>
                                <h5 class="text-secondary fw-semibold mb-2" style="font-size: 1.15rem;">Jumlah Pegawai</h5>
                                <h2 class="text-dark fw-bold display-5 mb-3">{{ $totalPegawai ?? 0 }}</h2>
                            </div>
                            <div class="d-flex justify-content-center gap-3 text-secondary flex-wrap" style="font-size: 0.85rem;">
                                @if(isset($pegawaiRoleCounts) && $pegawaiRoleCounts->isNotEmpty())
                                    @foreach($pegawaiRoleCounts as $rName => $rCount)
                                        @if(!$loop->first)
                                            <div style="border-left: 1px solid #dee2e6;"></div>
                                        @endif
                                        <div><strong class="text-dark d-block mb-1" style="font-size: 1.25rem;">{{ $rCount }}</strong> {{ $rName }}</div>
                                    @endforeach
                                @else
                                    <div><strong class="text-dark d-block mb-1" style="font-size: 1.25rem;">0</strong> Pegawai</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Jumlah Siswa (dark theme!) -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card shadow-sm border-0 text-white h-100" style="background-color: #212529; border-radius: 12px;">
                        <div class="card-body pt-4 text-center d-flex flex-column justify-content-between">
                            <div>
                                <h5 class="text-white-50 fw-semibold mb-2" style="font-size: 1.15rem;">Jumlah Siswa</h5>
                                <h2 class="text-white fw-bold display-5 mb-3">{{ $totalSiswa ?? 0 }}</h2>
                            </div>
                            <div class="d-flex justify-content-center gap-3 text-white-50 px-2" style="font-size: 0.85rem;">
                                @for ($t = 1; $t <= 6; $t++)
                                    <div>
                                        <strong class="text-white d-block mb-1" style="font-size: 1.25rem;">{{ $siswaCountByTingkat[$t] ?? 0 }}</strong>
                                        {{ $t == 1 ? 'I' : ($t == 2 ? 'II' : ($t == 3 ? 'III' : ($t == 4 ? 'IV' : ($t == 5 ? 'V' : 'VI')))) }}
                                    </div>
                                    @if ($t < 6)
                                        <div style="border-left: 1px solid rgba(255,255,255,0.15);"></div>
                                    @endif
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Kehadiran Siswa -->
                <div class="col-lg-4 col-md-12 mb-4">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                        <div class="card-body pt-4 text-center d-flex flex-column justify-content-between">
                            <div>
                                <h5 class="text-secondary fw-semibold mb-2" style="font-size: 1.15rem;">Kehadiran Siswa</h5>
                                <h2 class="text-dark fw-bold display-5 mb-3">{{ $hadirCount ?? 0 }}</h2>
                            </div>
                            <p class="text-secondary mb-0 fw-semibold" style="font-size: 0.95rem;">
                                @if(($kehadiranTotal ?? 0) > 0)
                                    {{ $kehadiranPercentage }}% Total Kehadiran
                                @else
                                    0% (Belum ada data kehadiran)
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Row 2: 3 Columns -->
                <!-- Card 4: Akademik -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                        <div class="card-body pt-4 text-center d-flex flex-column justify-content-between">
                            <h5 class="text-secondary fw-semibold mb-2" style="font-size: 1.15rem;">Akademik</h5>
                            <h2 class="text-dark fw-bold display-5 mb-2">{{ $akademikPercentage }}%</h2>
                            <div class="d-flex flex-column align-items-center">
                                <p class="text-secondary mb-1 fw-bold" style="font-size: 0.95rem;">
                                    @if($akademikAvg !== null && $akademikAvg > 0)
                                        {{ $akademikAvg >= 85 ? 'Sangat Baik' : ($akademikAvg >= 75 ? 'Baik' : 'Cukup') }}
                                    @else
                                        Belum Ada Data Nilai
                                    @endif
                                </p>
                                <p class="text-secondary mb-0" style="font-size: 0.85rem;">
                                    @if($akademikAvg !== null && $akademikAvg > 0)
                                        Rata-rata Nilai Siswa
                                    @else
                                        0% (Data nilai belum terisi)
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 5: Grafik Akademik -->
                <div class="col-lg-5 col-md-6 mb-4">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                        <div class="card-body pt-4">
                            <h5 class="card-title text-dark fw-bold mb-4 p-0">Grafik Akademik</h5>
                            <canvas id="akademikChart" style="max-height: 180px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Card 6: Profile Sekolah -->
                <div class="col-lg-4 col-md-12 mb-4">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                        <div class="card-body pt-4 text-center">
                            <h5 class="card-title text-dark fw-bold mb-3 p-0">Profile Sekolah</h5>
                            
                            <div class="mb-3 d-flex justify-content-center">
                                @if ($schoolProfile && $schoolProfile->logo_sekolah)
                                    <img src="{{ asset($schoolProfile->logo_sekolah) }}" alt="Logo Sekolah" class="img-fluid" style="max-height: 100px; object-fit: contain;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-light rounded" style="width: 100px; height: 100px;">
                                        <i class="bi bi-image text-secondary" style="font-size: 2.5rem;"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="text-start px-2">
                                <table class="table table-sm table-borderless text-dark mb-0" style="font-size: 0.85rem;">
                                    <tr>
                                        <td class="fw-semibold text-secondary" style="width: 110px; padding: 4px 0;">Nama Sekolah</td>
                                        <td style="width: 10px; padding: 4px 0;">:</td>
                                        <td class="fw-bold" style="padding: 4px 0;">{{ $schoolProfile->nama_sekolah ?? 'SD Negeri 007 Sekupang' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-secondary" style="padding: 4px 0;">NPSN</td>
                                        <td style="padding: 4px 0;">:</td>
                                        <td class="fw-bold" style="padding: 4px 0;">{{ $schoolProfile->nis_nss_nds ?? '10403456' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-secondary" style="padding: 4px 0;">Alamat</td>
                                        <td style="padding: 4px 0;">:</td>
                                        <td class="fw-bold" style="padding: 4px 0;">{{ $schoolProfile->alamat_sekolah ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-secondary" style="padding: 4px 0;">Email</td>
                                        <td style="padding: 4px 0;">:</td>
                                        <td class="fw-bold text-primary" style="padding: 4px 0;">{{ $schoolProfile->email ?? 'sdn007sekupang@gmail.com' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-secondary" style="padding: 4px 0;">Kepala Sekolah</td>
                                        <td style="padding: 4px 0;">:</td>
                                        <td class="fw-bold" style="padding: 4px 0;">{{ $schoolProfile->nama_kepala_sekolah ?? 'Yusal, S.Pd.' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
        @else
            <div class="row">

                <!-- Left side columns -->
                <div class="col-lg-8">
                    <div class="row">

                        <!-- Selamat Datang -->
                        <div class="col-12 mb-3">
                            <div class="card border-0"
                                style="background: linear-gradient(135deg, #0d2a6e, #1a4fad); color: #fff; border-radius: 16px;">
                                <div class="card-body py-4 px-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-white"
                                            style="width:56px;height:56px;font-size:24px;font-weight:700;color:#1a4fad;flex-shrink:0;">
                                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <h5 class="mb-1 text-white">
                                                Selamat Datang, {{ $user->name ?? 'Pengguna' }}!
                                            </h5>
                                            <p class="mb-0" style="opacity:.8;font-size:13px;">
                                                <i class="bi bi-shield-check me-1"></i>
                                                Role: <strong class="text-capitalize">{{ $activeRole ?: '-' }}</strong>
                                                @if (($user->roles ?? '') === 'guru' && $user->isWaliKelasAktif())
                                                    <span class="badge bg-light text-dark ms-1" style="font-size:11px;">Guru & Wali Kelas</span>
                                                @endif
                                                &nbsp;·&nbsp; SIAKAD SD Negeri 007 Sekupang
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistik Cards -->
                        @if (in_array($activeRole, ['admin', 'kepala sekolah']))
                            <div class="col-xxl-4 col-md-6">
                                <div class="card info-card" style="border-left: 4px solid #4154f1;">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Siswa</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"
                                                style="background:#e8eafd;">
                                                <i class="bi bi-person-lines-fill" style="color:#4154f1;"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>{{ $totalSiswa ?? '—' }}</h6>
                                                <span class="text-muted small">Data dari database</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- End Siswa Card -->

                            <div class="col-xxl-4 col-md-6">
                                <div class="card info-card" style="border-left: 4px solid #2eca6a;">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Guru</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"
                                                style="background:#e0f8ec;">
                                                <i class="bi bi-person-badge" style="color:#2eca6a;"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>{{ $totalGuru ?? '—' }}</h6>
                                                <span class="text-muted small">Data dari database</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- End Guru Card -->

                            <div class="col-xxl-4 col-md-6">
                                <div class="card info-card" style="border-left: 4px solid #ff771d;">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Kelas</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"
                                                style="background:#fff0e6;">
                                                <i class="bi bi-building" style="color:#ff771d;"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>{{ $totalKelas ?? '—' }}</h6>
                                                <span class="text-muted small">Data dari database</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- End Kelas Card -->
                        @endif

                        @if (in_array($activeRole, ['guru', 'wali kelas']))
                            <div class="col-xxl-6 col-md-6">
                                <div class="card info-card" style="border-left: 4px solid #4154f1;">
                                    <div class="card-body">
                                        <h5 class="card-title">Kelas Saya</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"
                                                style="background:#e8eafd;">
                                                <i class="bi bi-building" style="color:#4154f1;"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6 style="font-size: 1.1rem;">{{ $guruKelasDisplay ?? '—' }}</h6>
                                                <span class="text-muted small">
                                                    @if(!empty($kelasWaliNama))
                                                        Wali Kelas {{ $kelasWaliNama }} ({{ $guruKelasCount ?? 1 }} Kelas)
                                                    @else
                                                        {{ ($guruKelasCount ?? 0) > 0 ? ($guruKelasCount . ' Kelas Diampu') : 'Kelas yang diampu' }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xxl-6 col-md-6">
                                <div class="card info-card" style="border-left: 4px solid #2eca6a;">
                                    <div class="card-body">
                                        <h5 class="card-title">Mata Pelajaran</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"
                                                style="background:#e0f8ec;">
                                                <i class="bi bi-book" style="color:#2eca6a;"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6 style="font-size: 1.1rem;">{{ $guruMapelDisplay ?? '—' }}</h6>
                                                <span class="text-muted small">{{ ($guruMapelCount ?? 0) > 0 ? ($guruMapelCount . ' Mapel Aktif') : 'Mapel yang diajarkan' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (in_array($activeRole, ['siswa', 'orang tua']))
                            <div class="col-xxl-4 col-md-6">
                                <div class="card info-card" style="border-left: 4px solid #4154f1;">
                                    <div class="card-body">
                                        <h5 class="card-title">Rata-rata Nilai</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"
                                                style="background:#e8eafd;">
                                                <i class="bi bi-journal-check" style="color:#4154f1;"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>—</h6>
                                                <span class="text-muted small">Semester ini</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xxl-4 col-md-6">
                                <div class="card info-card" style="border-left: 4px solid #2eca6a;">
                                    <div class="card-body">
                                        <h5 class="card-title">Kehadiran</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"
                                                style="background:#e0f8ec;">
                                                <i class="bi bi-calendar-check" style="color:#2eca6a;"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>—</h6>
                                                <span class="text-muted small">Bulan ini</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xxl-4 col-md-6">
                                <div class="card info-card" style="border-left: 4px solid #ff771d;">
                                    <div class="card-body">
                                        <h5 class="card-title">Ekskul Diikuti</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"
                                                style="background:#fff0e6;">
                                                <i class="bi bi-trophy" style="color:#ff771d;"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>—</h6>
                                                <span class="text-muted small">Kegiatan aktif</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Pengumuman Terbaru -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Pengumuman Terbaru</h5>
                                    <div class="text-center py-4 text-muted">
                                        <i class="bi bi-megaphone" style="font-size:40px;opacity:.3;"></i>
                                        <p class="mt-2 mb-0">Belum ada pengumuman saat ini.</p>
                                    </div>
                                </div>
                            </div>
                        </div><!-- End Pengumuman -->

                    </div>
                </div><!-- End Left side columns -->

                <!-- Right side columns -->
                <div class="col-lg-4">

                    <!-- Info Pengguna -->
                    <div class="card">
                        <div class="card-body pt-4">
                            <h5 class="card-title">Informasi Akun</h5>
                            <div class="text-center mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white"
                                    style="width:72px;height:72px;font-size:28px;font-weight:700;">
                                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                </div>
                                <h6 class="mt-2 mb-1">{{ $user->name ?? '-' }}</h6>
                                <p class="text-muted small mb-0">{{ $user->email ?? '-' }}</p>
                                <span class="badge bg-primary mt-1 text-capitalize">{{ $activeRole ?: '-' }}</span>
                            </div>
                            <hr>
                            <ul class="list-unstyled small">
                                <li class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-muted"><i class="bi bi-person me-2"></i>Nama</span>
                                    <strong>{{ $user->name ?? '-' }}</strong>
                                </li>
                                <li class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-muted"><i class="bi bi-envelope me-2"></i>Email</span>
                                    <strong>{{ $user->email ?? '-' }}</strong>
                                </li>
                                <li class="d-flex justify-content-between py-1">
                                    <span class="text-muted"><i class="bi bi-shield-check me-2"></i>Role</span>
                                    <strong class="text-capitalize">{{ $activeRole ?: '-' }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div><!-- End Info Pengguna -->

                    <!-- Info Sekolah -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Info Sekolah</h5>
                            <ul class="list-unstyled small">
                                <li class="d-flex align-items-start gap-2 py-2 border-bottom">
                                    <i class="bi bi-building text-primary mt-1"></i>
                                    <div>
                                        <strong>SD Negeri 007 Sekupang</strong><br>
                                        <span class="text-muted">Kota Batam, Kepulauan Riau</span>
                                    </div>
                                </li>
                                <li class="d-flex align-items-start gap-2 py-2 border-bottom">
                                    <i class="bi bi-calendar3 text-primary mt-1"></i>
                                    <div>
                                        <strong>Tahun Ajaran</strong><br>
                                        <span class="text-muted">{{ date('Y') }}/{{ date('Y') + 1 }}</span>
                                    </div>
                                </li>
                                <li class="d-flex align-items-start gap-2 py-2">
                                    <i class="bi bi-clock text-primary mt-1"></i>
                                    <div>
                                        <strong>Waktu Login</strong><br>
                                        <span
                                            class="text-muted">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y · HH:mm') }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div><!-- End Info Sekolah -->

                </div><!-- End Right side columns -->

            </div>
        @endif
    </section>
@endsection

@push('script')
    @if (($activeRole ?? '') === 'kepala sekolah')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const ctx = document.getElementById('akademikChart').getContext('2d');
            
            const chartLabels = {!! json_encode($chartLabels ?? []) !!};
            const chartDataValues = {!! json_encode($chartValues ?? []) !!};

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Rata-rata Nilai Kelas',
                        data: chartDataValues,
                        backgroundColor: '#212529',
                        borderColor: '#212529',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                color: '#6c757d'
                            },
                            grid: {
                                borderDash: [5, 5]
                            }
                        },
                        x: {
                            ticks: {
                                color: '#6c757d'
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
    @endif
@endpush
