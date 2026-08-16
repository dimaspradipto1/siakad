@extends('layouts.dashboard.template')

@section('title', 'Cetak Raport')

@section('content')
    <div class="pagetitle">
        <h1 class="text-primary fw-bold">Cetak Raport</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('nilai.index') }}">Nilai</a></li>
                <li class="breadcrumb-item active">Cetak Raport</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body pt-4">
                        <h5 class="card-title text-primary fw-bold mb-3 p-0">Form Cetak Raport</h5>
                        
                        <form action="{{ route('nilai.cetak-raport') }}" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="tahun_ajaran_id" class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                                <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select" required>
                                    <option value="" disabled selected>-- Pilih Tahun Ajaran --</option>
                                    @foreach($tahunAjarans as $ta)
                                        <option value="{{ $ta->id }}" {{ $selectedTa == $ta->id ? 'selected' : '' }}>
                                            {{ $ta->nama_tahun_ajaran }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="semester_name" class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                                <select name="semester_name" id="semester_name" class="form-select" required>
                                    <option value="" disabled {{ empty($selectedSemName) ? 'selected' : '' }}>-- Pilih Semester --</option>
                                    @if(isset($semesters))
                                        @foreach($semesters as $sem)
                                            <option value="{{ $sem->nama_semester }}" {{ $selectedSemName == $sem->nama_semester ? 'selected' : '' }}>
                                                {{ $sem->nama_semester }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="kelas_id" class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                                <select name="kelas_id" id="kelas_id" class="form-select" required>
                                    <option value="" disabled selected>-- Pilih Kelas --</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" {{ $selectedKelas == $k->id ? 'selected' : '' }}>
                                            {{ $k->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                                <a href="{{ route('nilai.cetak-raport') }}" class="btn btn-secondary text-white btn-sm px-3" style="background-color: #6c757d; border-color: #6c757d;">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm px-3" style="background-color: #0d6efd; border-color: #0d6efd;">
                                    <i class="bi bi-search"></i> Tampilkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if($selectedTa && $selectedSem && $selectedKelas)
                <div class="card shadow-sm border-0">
                    <div class="card-body pt-4">
                        <h5 class="card-title text-primary fw-bold p-0 mb-3">Daftar Siswa Kelas</h5>

                        @if(count($students) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle text-center">
                                <thead class="table-light fw-bold text-dark">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th style="width: 150px;">NISN</th>
                                        <th class="text-start">Nama Siswa</th>
                                        <th style="width: 150px;">Kelas</th>
                                        <th style="width: 200px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $index => $siswa)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $siswa->nisn }}</td>
                                            <td class="text-start fw-semibold">{{ $siswa->nama_siswa }}</td>
                                            <td>{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                            <td>
                                                <a href="{{ route('nilai.cetak-raport.print', [$siswa->id, $selectedTa, $selectedSem]) }}" 
                                                   target="_blank" 
                                                   class="btn btn-success btn-sm px-3">
                                                    <i class="bi bi-printer-fill me-1"></i> Cetak Raport
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-warning text-center my-3"><i class="bi bi-exclamation-triangle-fill"></i> Tidak ada data siswa yang terdaftar di kelas ini.</div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>
@endsection
