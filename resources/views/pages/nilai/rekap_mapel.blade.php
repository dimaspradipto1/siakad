@extends('layouts.dashboard.template')

@section('title', 'Rekap Nilai Siswa')

@section('content')
    <div class="pagetitle">
        <h1 class="text-primary fw-bold">Rekap Nilai Siswa</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Rekap Nilai Siswa</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        <h5 class="card-title text-dark fw-bold mb-4 p-0">Form Rekap Nilai Siswa</h5>
                        
                        <form action="{{ route('nilai.rekap-mapel') }}" method="GET" class="row g-4">
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
                                    <option value="" disabled selected></option>
                                    <option value="Semester 1 (Ganjil)" {{ (isset($selectedSemName) && $selectedSemName == 'Semester 1 (Ganjil)') || (!isset($selectedSemName) && request('semester_name') == 'Semester 1 (Ganjil)') ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                                    <option value="Semester 2 (Genap)" {{ (isset($selectedSemName) && $selectedSemName == 'Semester 2 (Genap)') || (!isset($selectedSemName) && request('semester_name') == 'Semester 2 (Genap)') ? 'selected' : '' }}>Semester 2 (Genap)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="kelas_id" class="form-label fw-semibold text-dark">Kelas</label>
                                <select name="kelas_id" id="kelas_id" class="form-select py-2" style="border-radius: 8px;" required>
                                    <option value="" disabled selected></option>
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
                                    <option value="" disabled selected></option>
                                    @foreach($mapels as $mp)
                                        <option value="{{ $mp->id }}" {{ $selectedMapel == $mp->id ? 'selected' : '' }}>
                                            {{ $mp->nama_mata_pelajaran }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 d-flex justify-content-end align-items-center gap-4 pt-2">
                                <a href="{{ route('nilai.rekap-mapel') }}" class="text-dark fw-bold text-decoration-none small" style="font-size: 0.95rem;">
                                    Reset
                                </a>
                                <button type="submit" class="btn btn-dark px-4 py-2" style="background-color: #212529; border-color: #212529; border-radius: 8px; font-weight: bold; font-size: 0.95rem;">
                                    Tampilkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if($selectedTa && $selectedSem && $selectedKelas && $selectedMapel)
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title text-dark fw-bold p-0 mb-0">Daftar Nilai Siswa</h5>
                            <div class="d-flex gap-2">
                                <a href="{{ route('nilai.rekap-mapel.export', ['tahun_ajaran_id' => $selectedTa, 'semester_name' => $selectedSemName, 'kelas_id' => $selectedKelas, 'mata_pelajaran_id' => $selectedMapel]) }}" class="btn btn-success btn-sm rounded-3 fw-bold" title="Export Excel Rekap Mapel">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                                </a>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Cetak Rekap</button>
                            </div>
                        </div>

                        @if(count($students) > 0)
                        <div class="table-responsive">
                            <table class="table align-middle text-center table-bordered table-hover" id="rekap-mapel-table" style="width:100%">
                                <thead class="table-light fw-bold text-dark">
                                    <tr>
                                        <th>NISN</th>
                                        <th class="text-start">Nama Siswa</th>
                                        <th>Nilai Harian</th>
                                        <th>Nilai MID+</th>
                                        <th>Nilai PAS+</th>
                                        <th>Nilai Rata2</th>
                                        <th>Nilai Raport</th>
                                        <th class="text-start">TP Optimal</th>
                                        <th class="text-start">TP Yang Perlu Peningkatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $siswa)
                                        @php
                                            $rec = $siswa->nilai_record;

                                            $harian = $rec && $rec->nilai_harian !== null ? floatval($rec->nilai_harian) : null;
                                            $midPlus = $rec && $rec->nilai_mid_plus !== null ? floatval($rec->nilai_mid_plus) : null;
                                            $pasPlus = $rec && $rec->nilai_pas_plus !== null ? floatval($rec->nilai_pas_plus) : null;
                                            $rata2 = $siswa->nilai_rata2_calc;
                                            $raport = $rec && $rec->nilai_raport !== null ? floatval($rec->nilai_raport) : null;

                                            if (!function_exists('formatVal')) {
                                                function formatVal($val) {
                                                    if ($val === null) return '-';
                                                    return $val == intval($val) ? intval($val) : number_format($val, 1);
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td class="text-dark">{{ $siswa->nisn }}</td>
                                            <td class="text-start fw-bold text-dark">{{ $siswa->nama_siswa }}</td>
                                            <td>{{ formatVal($harian) }}</td>
                                            <td>{{ formatVal($midPlus) }}</td>
                                            <td>{{ formatVal($pasPlus) }}</td>
                                            <td class="fw-semibold text-primary">{{ formatVal($rata2) }}</td>
                                            <td class="fw-bold text-dark">{{ formatVal($raport) }}</td>
                                            <td class="text-start">{{ $rec && $rec->tp_optimal ? $rec->tp_optimal : '-' }}</td>
                                            <td class="text-start">{{ $rec && $rec->tp_perlu_peningkatan ? $rec->tp_perlu_peningkatan : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-warning text-center my-3"><i class="bi bi-exclamation-triangle-fill"></i> Tidak ada data siswa ditemukan.</div>
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
        if ($('#rekap-mapel-table').length) {
            $('#rekap-mapel-table').DataTable({
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
