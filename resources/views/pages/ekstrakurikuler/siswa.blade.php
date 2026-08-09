@extends('layouts.dashboard.template')

@section('title', 'Ekstrakurikuler Siswa')

@section('content')
    <div class="pagetitle">
        <h1 class="text-primary fw-bold">Ekstrakurikuler Siswa</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Input Ekstrakurikuler</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        <h5 class="card-title text-dark fw-bold mb-4 p-0">Form Ekstrakurikuler Siswa</h5>
                        
                        <form action="{{ route('ekstrakurikuler.siswa') }}" method="GET" class="row g-4">
                            <div class="col-md-4">
                                <label for="tahun_ajaran_id" class="form-label fw-semibold text-dark">Tahun Ajaran <span class="text-danger">*</span></label>
                                <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select py-2" style="border-radius: 8px;" required>
                                    <option value="" disabled selected></option>
                                    @foreach($tahunAjarans as $ta)
                                        <option value="{{ $ta->id }}" {{ $selectedTa == $ta->id ? 'selected' : '' }}>
                                            {{ $ta->nama_tahun_ajaran }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="semester_name" class="form-label fw-semibold text-dark">Semester <span class="text-danger">*</span></label>
                                <select name="semester_name" id="semester_name" class="form-select py-2" style="border-radius: 8px;" required>
                                    <option value="" disabled selected></option>
                                    <option value="Semester 1 (Ganjil)" {{ $selectedSemName == 'Semester 1 (Ganjil)' ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                                    <option value="Semester 2 (Genap)" {{ $selectedSemName == 'Semester 2 (Genap)' ? 'selected' : '' }}>Semester 2 (Genap)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="kelas_id" class="form-label fw-semibold text-dark">Kelas <span class="text-danger">*</span></label>
                                <select name="kelas_id" id="kelas_id" class="form-select py-2" style="border-radius: 8px;" required>
                                    <option value="" disabled selected></option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" {{ $selectedKelas == $k->id ? 'selected' : '' }}>
                                            {{ $k->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 d-flex justify-content-end align-items-center gap-4 pt-2">
                                <a href="{{ route('ekstrakurikuler.siswa') }}" class="text-dark fw-bold text-decoration-none small" style="font-size: 0.95rem;">
                                    Reset
                                </a>
                                <button type="submit" class="btn btn-dark px-4 py-2" style="background-color: #212529; border-color: #212529; border-radius: 8px; font-weight: bold; font-size: 0.95rem;">
                                    Get Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if($selectedTa && $selectedSem && $selectedKelas)
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        @if(count($students) > 0)
                        <form action="{{ route('ekstrakurikuler.siswa.save') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedTa }}">
                            <input type="hidden" name="semester_id" value="{{ $selectedSem }}">

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle text-center" style="overflow: visible;">
                                    <thead class="table-light fw-bold text-dark">
                                        <tr>
                                            <th style="width: 60px;">No</th>
                                            <th style="width: 150px;">NISN</th>
                                            <th class="text-start">Nama Siswa</th>
                                            <th style="width: 150px;">Kelas</th>
                                            <th class="text-start" style="width: 350px;">Ekskul</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $index => $siswa)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $siswa->nisn }}</td>
                                                <td class="text-start fw-semibold">{{ $siswa->nama_siswa }}</td>
                                                <td>{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                                 <td class="text-start" style="overflow: visible;">
                                                    <select name="ekskul[{{ $siswa->id }}][]" class="form-select select2-ekskul" multiple data-placeholder="Pilih Ekskul">
                                                        @foreach($ekskuls as $ekskul)
                                                            <option value="{{ $ekskul->id }}" {{ in_array($ekskul->id, $siswa->assigned_ekskuls) ? 'selected' : '' }}>
                                                                {{ $ekskul->nama_ekskul }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="form-text text-muted fw-semibold">
                                    Pilihan ekskul bisa lebih dari 1 dan merujuk ke tabel master ekstrakurikuler.
                                </div>
                                <button type="submit" class="btn btn-dark px-4 py-2" style="background-color: #212529; border-color: #212529; border-radius: 8px; font-weight: bold;">
                                    Simpan
                                </button>
                            </div>
                        </form>
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

@push('script')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2-ekskul').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Pilih Ekskul',
                    allowClear: true,
                    closeOnSelect: false
                });
            }
        });
    </script>
@endpush
