@extends('layouts.dashboard.template')

@section('title', 'Detail Mata Pelajaran')

@section('content')
    <div class="pagetitle">
        <h1 class="text-primary fw-bold">Detail Mata Pelajaran</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('matapelajaran.index') }}">Mata Pelajaran</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body pt-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title text-primary fw-bold p-0 m-0">
                                <i class="bi bi-info-circle-fill me-1"></i> Informasi Mata Pelajaran
                            </h5>
                            @if(auth()->user()->roles === 'admin')
                                <a href="{{ route('matapelajaran.edit', $matapelajaran->id) }}" class="btn btn-warning btn-sm text-dark px-3 fw-semibold">
                                    <i class="bi bi-pencil me-1"></i> Edit Data
                                </a>
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle">
                                <tbody>
                                    <tr>
                                        <th style="width: 30%;" class="bg-light fw-bold text-dark">Kode Mapel</th>
                                        <td class="fw-semibold text-primary">{{ $matapelajaran->kode_mapel }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light fw-bold text-dark">Nama Mapel</th>
                                        <td class="fw-bold text-dark">{{ $matapelajaran->nama_mata_pelajaran }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light fw-bold text-dark">KKM</th>
                                        <td>
                                            <span class="badge bg-success px-3 py-2 fs-6 fw-bold">{{ $matapelajaran->kkm }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light fw-bold text-dark">Guru Pengajar</th>
                                        <td class="fw-semibold">{{ $matapelajaran->guru && $matapelajaran->guru->pegawai ? $matapelajaran->guru->pegawai->nama_pegawai : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light fw-bold text-dark">Kelas</th>
                                        <td>
                                            <span class="badge bg-primary px-3 py-2 fs-7">{{ $matapelajaran->kelas ? $matapelajaran->kelas->nama_kelas : '-' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light fw-bold text-dark">Tahun Ajaran</th>
                                        <td class="fw-semibold">{{ $matapelajaran->tahunAjaran ? $matapelajaran->tahunAjaran->nama_tahun_ajaran : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light fw-bold text-dark">Semester</th>
                                        <td class="fw-semibold">{{ $matapelajaran->semester ? $matapelajaran->semester->nama_semester : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light fw-bold text-dark">Hari & Jam Mengajar</th>
                                        <td>
                                            <i class="bi bi-calendar3 me-1 text-secondary"></i> {{ $matapelajaran->hari_mengajar ?: '-' }}
                                            <span class="mx-2 text-muted">|</span>
                                            <i class="bi bi-clock me-1 text-secondary"></i> {{ $matapelajaran->jam_mengajar ?: '-' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <h6 class="fw-bold text-success"><i class="bi bi-check-circle-fill me-1"></i> TP yang Optimal (Pencapaian Tinggi)</h6>
                            <div class="p-3 border rounded bg-light text-dark mb-3" style="font-size: 0.95rem; line-height: 1.6;">
                                {{ $matapelajaran->tp_optimal ?: 'Belum diisi' }}
                            </div>
                        </div>

                        <div class="mt-3 mb-4">
                            <h6 class="fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> TP Yang Perlu Peningkatan (Butuh Bimbingan)</h6>
                            <div class="p-3 border rounded bg-light text-dark" style="font-size: 0.95rem; line-height: 1.6;">
                                {{ $matapelajaran->tp_peningkatan ?: 'Belum diisi' }}
                            </div>
                        </div>

                        <div class="d-flex justify-content-start border-top pt-3">
                            <a href="{{ route('matapelajaran.index') }}" class="btn btn-secondary px-4">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
