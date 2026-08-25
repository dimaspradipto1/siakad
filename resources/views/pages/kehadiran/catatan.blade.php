@extends('layouts.dashboard.template')

@section('title', 'Daftar Catatan Kehadiran Siswa - ' . ($siswa->nama_siswa ?? ''))

@section('content')
    <div class="pagetitle">
        <h1 class="text-primary fw-bold">Daftar Catatan Kehadiran</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('kehadiran.rekap') }}">Rekap Kehadiran</a></li>
                <li class="breadcrumb-item active">Catatan Kehadiran Siswa</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <!-- Info Siswa & Filter Card -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 mb-3 border-bottom">
                            <div>
                                <h4 class="fw-bold text-dark mb-1">{{ $siswa->nama_siswa }}</h4>
                                <div class="text-secondary small">
                                    <span class="me-3"><i class="bi bi-person-badge me-1"></i> NISN: <strong>{{ $siswa->nisn ?? '-' }}</strong></span>
                                    <span class="me-3"><i class="bi bi-building me-1"></i> Kelas: <strong>{{ $kelasModel->nama_kelas ?? ($siswa->kelas->nama_kelas ?? '-') }}</strong></span>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-2 mt-md-0">
                                <button onclick="window.close()" class="btn btn-secondary btn-sm px-3" style="border-radius: 8px;">
                                    <i class="bi bi-x-circle me-1"></i> Tutup Tab
                                </button>
                                <button onclick="window.print()" class="btn btn-dark btn-sm px-3" style="border-radius: 8px;">
                                    <i class="bi bi-printer-fill me-1"></i> Cetak
                                </button>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6 col-md-3">
                                <div class="p-3 bg-light rounded-3">
                                    <div class="text-muted small fw-semibold">Mata Pelajaran</div>
                                    <div class="text-dark fw-bold mt-1">{{ $mapelModel->nama_mata_pelajaran ?? 'Semua Mapel' }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="p-3 bg-light rounded-3">
                                    <div class="text-muted small fw-semibold">Tahun Ajaran</div>
                                    <div class="text-dark fw-bold mt-1">{{ $tahunAjaran->nama_tahun_ajaran ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="p-3 bg-light rounded-3">
                                    <div class="text-muted small fw-semibold">Semester</div>
                                    <div class="text-dark fw-bold mt-1">{{ $selectedSemName ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="p-3 bg-light rounded-3">
                                    <div class="text-muted small fw-semibold">Bulan</div>
                                    <div class="text-dark fw-bold mt-1">{{ $selectedBulanName ?? 'Semua Bulan' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Status Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-2 col-6">
                        <div class="card shadow-sm border-0 mb-0 h-100" style="border-radius: 12px;">
                            <div class="card-body py-3 px-3 text-center">
                                <div class="text-muted small fw-semibold">Total Pertemuan</div>
                                <div class="fs-4 fw-bold text-dark mt-1">{{ $counts['total'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="card shadow-sm border-0 mb-0 h-100" style="border-radius: 12px; border-left: 4px solid #198754 !important;">
                            <div class="card-body py-3 px-3 text-center">
                                <div class="text-success small fw-semibold">Hadir</div>
                                <div class="fs-4 fw-bold text-success mt-1">{{ $counts['hadir'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="card shadow-sm border-0 mb-0 h-100" style="border-radius: 12px; border-left: 4px solid #ffc107 !important;">
                            <div class="card-body py-3 px-3 text-center">
                                <div class="text-warning text-dark small fw-semibold">Sakit</div>
                                <div class="fs-4 fw-bold text-warning text-dark mt-1">{{ $counts['sakit'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="card shadow-sm border-0 mb-0 h-100" style="border-radius: 12px; border-left: 4px solid #0dcaf0 !important;">
                            <div class="card-body py-3 px-3 text-center">
                                <div class="text-info text-dark small fw-semibold">Izin</div>
                                <div class="fs-4 fw-bold text-info text-dark mt-1">{{ $counts['izin'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="card shadow-sm border-0 mb-0 h-100" style="border-radius: 12px; border-left: 4px solid #dc3545 !important;">
                            <div class="card-body py-3 px-3 text-center">
                                <div class="text-danger small fw-semibold">Alpa</div>
                                <div class="fs-4 fw-bold text-danger mt-1">{{ $counts['alpa'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="card shadow-sm border-0 mb-0 h-100" style="border-radius: 12px; border-left: 4px solid #0d6efd !important;">
                            <div class="card-body py-3 px-3 text-center">
                                <div class="text-primary small fw-semibold">Ada Catatan</div>
                                <div class="fs-4 fw-bold text-primary mt-1">{{ $counts['catatan'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Catatan -->
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title text-dark fw-bold m-0 p-0">Riwayat & Catatan Kehadiran</h5>
                            <div class="small fw-semibold text-secondary">
                                Keterangan: 
                                <span class="badge bg-success ms-1">Hadir</span>
                                <span class="badge bg-warning text-dark ms-1">Sakit</span>
                                <span class="badge bg-info text-dark ms-1">Izin</span>
                                <span class="badge bg-danger ms-1">Alpa</span>
                            </div>
                        </div>

                        @if(count($records) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light fw-bold text-dark text-center">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th style="width: 200px;">Hari & Tanggal</th>
                                        <th style="width: 120px;">Kehadiran</th>
                                        <th style="width: 160px;">Jenis Catatan</th>
                                        <th>Catatan / Keterangan</th>
                                        <th style="width: 160px;">Tindak Lanjut / Guru</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($records as $index => $item)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td class="text-center fw-semibold">
                                                {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') : '-' }}
                                            </td>
                                            <td class="text-center">
                                                @if($item->jenis_kehadiran === 'Hadir')
                                                    <span class="badge bg-success px-2 py-1">Hadir</span>
                                                @elseif($item->jenis_kehadiran === 'Sakit')
                                                    <span class="badge bg-warning text-dark px-2 py-1">Sakit</span>
                                                @elseif($item->jenis_kehadiran === 'Izin')
                                                    <span class="badge bg-info text-dark px-2 py-1">Izin</span>
                                                @elseif($item->jenis_kehadiran === 'Alpa' || $item->jenis_kehadiran === 'Tanpa Keterangan')
                                                    <span class="badge bg-danger px-2 py-1">Alpa</span>
                                                @else
                                                    <span class="badge bg-secondary px-2 py-1">{{ $item->jenis_kehadiran }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($item->jenis_catatan)
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.8rem;">
                                                        {{ $item->jenis_catatan }}
                                                    </span>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($item->keterangan))
                                                    <div class="fw-semibold text-dark">{{ $item->keterangan }}</div>
                                                @elseif(!empty($item->isi_catatan))
                                                    <div class="fw-semibold text-dark">{{ $item->isi_catatan }}</div>
                                                @else
                                                    <span class="text-muted fst-italic small">Tidak ada catatan</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->tindak_lanjut)
                                                    <div class="small text-dark"><strong>TL:</strong> {{ $item->tindak_lanjut }}</div>
                                                @endif
                                                @if($item->guru)
                                                    <div class="small text-secondary"><i class="bi bi-person me-1"></i>{{ $item->guru }}</div>
                                                @endif
                                                @if(!$item->tindak_lanjut && !$item->guru)
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-warning text-center my-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Tidak ada data absensi untuk siswa ini pada periode yang dipilih.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        @media print {
            .pagetitle, nav, .btn, header, aside, footer {
                display: none !important;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
            }
            body {
                background-color: #fff !important;
            }
        }
    </style>
@endsection
