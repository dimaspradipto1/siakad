@extends('layouts.dashboard.template')

@section('title', 'Rekap Kehadiran Siswa')

@section('content')
    <div class="pagetitle">
        <h1 class="text-primary fw-bold">Rekap Kehadiran Siswa</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Rekap Kehadiran Siswa</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        <h5 class="card-title text-dark fw-bold mb-4 p-0">Form Rekap Kehadiran Siswa</h5>

                        <form action="{{ route('kehadiran.rekap') }}" method="GET" class="row g-4">
                            <div class="col-md-6">
                                <label for="tahun_ajaran_id" class="form-label fw-semibold text-dark">Tahun Ajaran</label>
                                <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select py-2" style="border-radius: 8px;" required>
                                    <option value="" disabled selected></option>
                                    @foreach($tahunAjarans as $ta)
                                        <option value="{{ $ta->id }}" {{ $selectedTa == $ta->id ? 'selected' : '' }}>
                                            {{ $ta->nama_tahun_ajaran }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="semester_name" class="form-label fw-semibold text-dark">Semester</label>
                                <select name="semester_name" id="semester_name" class="form-select py-2" style="border-radius: 8px;" required>
                                    <option value="" disabled {{ empty($selectedSemName) ? 'selected' : '' }}>-- Pilih Semester --</option>
                                    @php
                                        $semList = (isset($semesters) && $semesters->isNotEmpty()) 
                                            ? $semesters->pluck('nama_semester')->unique() 
                                            : collect(['Semester 1 (Ganjil)', 'Semester 2 (Genap)']);
                                    @endphp
                                    @foreach($semList as $semName)
                                        <option value="{{ $semName }}" {{ $selectedSemName == $semName ? 'selected' : '' }}>
                                            {{ $semName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="kelas_id" class="form-label fw-semibold text-dark">Kelas</label>
                                <select name="kelas_id" id="kelas_id" class="form-select py-2" style="border-radius: 8px;" required>
                                    <option value="" disabled selected>-- Pilih Kelas --</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" {{ $selectedKelas == $k->id ? 'selected' : '' }}>
                                            {{ $k->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="mata_pelajaran_id" class="form-label fw-semibold text-dark">Mata Pelajaran</label>
                                <select name="mata_pelajaran_id" id="mata_pelajaran_id" class="form-select py-2" style="border-radius: 8px;" required>
                                    <option value="" disabled selected>-- Pilih Mata Pelajaran --</option>
                                    @foreach($mapels as $mp)
                                        <option value="{{ $mp->id }}" {{ $selectedMapel == $mp->id ? 'selected' : '' }}>
                                            {{ $mp->nama_mata_pelajaran }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 d-flex justify-content-end align-items-center gap-4 pt-2">
                                <a href="{{ route('kehadiran.rekap') }}" class="text-dark fw-bold text-decoration-none small" style="font-size: 0.95rem;">
                                    Reset
                                </a>
                                <button type="submit" class="btn btn-dark px-4 py-2" style="background-color: #212529; border-color: #212529; border-radius: 8px; font-weight: bold; font-size: 0.95rem;">
                                    Tampilkan Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if($selectedTa && $selectedSem && $selectedKelas && $selectedMapel)
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title text-dark fw-bold m-0 p-0">Detail Kehadiran Siswa</h5>
                            <div class="small fw-semibold text-secondary">
                                Keterangan: 
                                <span class="badge bg-success ms-1">H</span> Hadir | 
                                <span class="badge bg-warning text-dark ms-1">S</span> Sakit | 
                                <span class="badge bg-info text-dark ms-1">I</span> Izin | 
                                <span class="badge bg-danger ms-1">A</span> Alpa
                            </div>
                        </div>

                        @if(count($students) > 0 && !empty($dates))
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle text-center">
                                <thead class="table-light fw-bold text-dark">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th style="width: 120px;">NISN</th>
                                        <th class="text-start" style="min-width: 180px;">Nama Siswa</th>
                                        @foreach($dates as $d)
                                            <th style="width: 45px;" title="{{ \Carbon\Carbon::parse($d)->translatedFormat('d F Y') }}">
                                                {{ \Carbon\Carbon::parse($d)->format('d') }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $index => $siswa)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $siswa->nisn }}</td>
                                            <td class="text-start fw-semibold">{{ $siswa->nama_siswa }}</td>
                                            @foreach($dates as $d)
                                                @php
                                                    $rec = $attendanceMatrix[$siswa->id][$d] ?? null;
                                                    $stName = $rec?->jenisKehadiran?->nama_kehadiran;
                                                @endphp
                                                <td>
                                                    @if($stName === 'Hadir')
                                                        <span class="badge bg-success">H</span>
                                                    @elseif($stName === 'Sakit')
                                                        <span class="badge bg-warning text-dark">S</span>
                                                    @elseif($stName === 'Izin')
                                                        <span class="badge bg-info text-dark">I</span>
                                                    @elseif($stName === 'Alpa' || $stName === 'Tanpa Keterangan')
                                                        <span class="badge bg-danger">A</span>
                                                    @elseif($stName)
                                                        <span class="badge bg-secondary">{{ strtoupper(substr($stName, 0, 1)) }}</span>
                                                    @else
                                                        <span class="text-muted small">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('kehadiran.rekap.print', request()->all()) }}" target="_blank" class="btn btn-dark px-4 py-2" style="background-color: #212529; border-color: #212529; border-radius: 8px; font-weight: bold;">
                                Cetak
                            </a>
                        </div>
                        @else
                        <div class="alert alert-warning text-center my-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Tidak ada data absensi yang pernah diisi untuk mata pelajaran ini.
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>
@endsection

