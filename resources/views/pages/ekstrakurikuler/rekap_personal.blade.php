@extends('layouts.dashboard.template')

@section('title', 'Rekap Ekstrakurikuler Siswa')

@section('content')
    <div class="pagetitle">
        <h1 class="text-primary fw-bold">Rekap Ekstrakurikuler</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Rekap Ekstrakurikuler</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body pt-4">
                        <h5 class="card-title text-primary fw-bold mb-3 p-0">Filter Rekap Ekstrakurikuler</h5>
                        
                        <form action="{{ route('ekstrakurikuler.personal') }}" method="GET" class="row g-3">
                            <div class="col-md-6">
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
                            
                            <div class="col-md-6">
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

                            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                                <a href="{{ route('ekstrakurikuler.personal') }}" class="btn btn-secondary text-white btn-sm px-3" style="background-color: #6c757d; border-color: #6c757d;">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                                </a>
                                <button type="submit" class="btn btn-dark btn-sm px-3" style="background-color: #212529; border-color: #212529;">
                                    <i class="bi bi-search"></i> Tampilkan Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if($selectedTa && $selectedSem)
                <div class="card shadow-sm border-0">
                    <div class="card-body pt-4">
                        <h5 class="card-title text-primary fw-bold p-0 mb-4">Kegiatan Ekstrakurikuler Diikuti</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle text-center">
                                <thead class="table-light text-dark fw-bold">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th class="text-start" style="width: 250px;">Kegiatan Ekstrakurikuler</th>
                                        <th class="text-start">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($ekskuls) > 0)
                                        @foreach($ekskuls as $index => $ekskul)
                                            @if($ekskul)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td class="text-start fw-bold text-dark">{{ $ekskul->nama_ekskul }}</td>
                                                    <td class="text-start">Aktif mengikuti kegiatan {{ strtolower($ekskul->nama_ekskul) }} dengan kriteria Sangat Baik.</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3" class="text-muted py-3">Tidak ada kegiatan ekstrakurikuler yang diikuti pada periode ini.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>
@endsection
