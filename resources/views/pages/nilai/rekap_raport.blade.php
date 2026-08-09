@extends('layouts.dashboard.template')

@php
if (!function_exists('abbreviateMapel')) {
    function abbreviateMapel($name) {
        $map = [
            'Pendidikan Agama Islam' => 'PAI',
            'Pendidikan Agama Islam dan Budi Pekerti' => 'PAI',
            'Pendidikan Pancasila dan Kewarganegaraan' => 'PKN',
            'Pendidikan Pancasila' => 'PKN',
            'Bahasa Indonesia' => 'B.INDO',
            'Matematika' => 'MTK',
            'Ilmu Pengetahuan Alam dan Sosial' => 'IPAS',
            'Ilmu Pengetahuan Alam' => 'IPA',
            'Ilmu Pengetahuan Sosial' => 'IPS',
            'Seni Budaya dan Prakarya' => 'SBDP',
            'Seni Budaya dan Musik' => 'SBDM',
            'Seni Rupa' => 'Seni Rupa',
            'Bahasa Inggris' => 'B.ING',
            'Pendidikan Jasmani, Olahraga, dan Kesehatan' => 'PJOK',
        ];
        return $map[$name] ?? $name;
    }
}
@endphp

@section('title', 'Rekap Nilai')

@section('content')
    <div class="pagetitle d-print-none">
        <h1 class="text-primary fw-bold">Rekap Nilai</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Rekap Nilai Raport</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0 mb-4 d-print-none" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        <h5 class="card-title text-dark fw-bold mb-4 p-0">Form Rekap Nilai</h5>
                        
                        <form action="{{ route('nilai.rekap-raport') }}" method="GET" class="row g-4">
                            <div class="col-md-6">
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

                            <div class="col-md-6">
                                <label for="semester_name" class="form-label fw-semibold text-dark">Semester <span class="text-danger">*</span></label>
                                <select name="semester_name" id="semester_name" class="form-select py-2" style="border-radius: 8px;" required>
                                    <option value="" disabled selected></option>
                                    <option value="Semester 1 (Ganjil)" {{ $selectedSemName == 'Semester 1 (Ganjil)' ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                                    <option value="Semester 2 (Genap)" {{ $selectedSemName == 'Semester 2 (Genap)' ? 'selected' : '' }}>Semester 2 (Genap)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
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
                                <a href="{{ route('nilai.rekap-raport') }}" class="text-dark fw-bold text-decoration-none small" style="font-size: 0.95rem;">
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
                        <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
                            <h5 class="card-title text-dark fw-bold p-0 mb-0">Rekap Nilai Raport Kelas</h5>
                            <a href="{{ route('nilai.rekap-raport.export', ['tahun_ajaran_id' => $selectedTa, 'semester_name' => $selectedSemName, 'kelas_id' => $selectedKelas, 'siswa_id' => $selectedSiswa]) }}" class="btn btn-success btn-sm px-3 rounded-3 fw-bold" title="Export Excel Rekap Raport">
                                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                            </a>
                        </div>

                        @if(count($students) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle text-center" id="rekap-raport-table" style="width:100%">
                                <thead class="table-light fw-bold text-dark">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th style="width: 120px;">NISN</th>
                                        <th class="text-start">Nama Siswa</th>
                                        @foreach($classMapels as $mp)
                                            <th>{{ abbreviateMapel($mp->nama_mata_pelajaran) }}</th>
                                        @endforeach
                                        <th class="table-success" style="width: 100px;">Rata2</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $index => $siswa)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $siswa->nisn }}</td>
                                            <td class="text-start fw-semibold">{{ $siswa->nama_siswa }}</td>
                                            @foreach($classMapels as $mp)
                                                <td>
                                                    {{ $siswa->grades[$mp->id] !== null ? intval($siswa->grades[$mp->id]) : '.....' }}
                                                </td>
                                            @endforeach
                                            <td class="table-success fw-bold">
                                                {{ $siswa->average !== null ? (floor($siswa->average) == $siswa->average ? intval($siswa->average) : number_format($siswa->average, 1)) : '.....' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-warning text-center my-3"><i class="bi bi-exclamation-triangle-fill"></i> Tidak ada data siswa atau mata pelajaran yang terdaftar.</div>
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
        if ($('#rekap-raport-table').length) {
            $('#rekap-raport-table').DataTable({
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                "language": {
                    "search": "Cari Siswa:",
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data siswa",
                    "infoEmpty": "Tidak ada data siswa",
                    "infoFiltered": "(disaring dari _MAX_ total siswa)",
                    "zeroRecords": "Data siswa tidak ditemukan",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Next &raquo;",
                        "previous": "&laquo; Prev"
                    }
                },
                "ordering": false
            });
        }
    });
</script>
@endpush
