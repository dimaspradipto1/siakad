@extends('layouts.dashboard.template')

@section('title', 'Rekap Nilai Siswa')

@section('content')
    <div class="pagetitle d-print-none">
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
                <div class="card shadow-sm border-0 mb-4 d-print-none" style="border-radius: 12px;">
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
                                    <option value="" disabled {{ empty($selectedSemName) ? 'selected' : '' }}>-- Pilih Semester --</option>
                                    @php
                                        $semList = (isset($semesters) && $semesters->isNotEmpty()) 
                                            ? $semesters->pluck('nama_semester')->unique() 
                                            : collect(['Semester 1 (Ganjil)', 'Semester 2 (Genap)']);
                                    @endphp
                                    @foreach($semList as $semName)
                                        <option value="{{ $semName }}" {{ ((isset($selectedSemName) && $selectedSemName == $semName) || (!isset($selectedSemName) && request('semester_name') == $semName)) ? 'selected' : '' }}>
                                            {{ $semName }}
                                        </option>
                                    @endforeach
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
                        <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
                            <h5 class="card-title text-dark fw-bold p-0 mb-0">Daftar Nilai Siswa</h5>
                            <div class="d-flex gap-2">
                                <a href="{{ route('nilai.rekap-mapel.export', ['tahun_ajaran_id' => $selectedTa, 'semester_name' => $selectedSemName, 'kelas_id' => $selectedKelas, 'mata_pelajaran_id' => $selectedMapel]) }}" class="btn btn-success btn-sm rounded-3 fw-bold" title="Export Excel Rekap Mapel">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                                </a>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Cetak Rekap</button>
                            </div>
                        </div>

                        @if(count($students) > 0)
                        @php
                            $currentTa = $tahunAjarans->firstWhere('id', $selectedTa);
                            $currentKelas = $kelas->firstWhere('id', $selectedKelas);
                            $currentMapel = $mapels->firstWhere('id', $selectedMapel);
                        @endphp
                        <div class="d-none d-print-block text-center mb-4">
                            <h4 class="fw-bold text-uppercase mb-1" style="color: #000;">REKAP NILAI SISWA PER MATA PELAJARAN</h4>
                            <p class="mb-0 fw-semibold" style="color: #000; font-size: 13px;">
                                Tahun Ajaran: {{ $currentTa->nama_tahun_ajaran ?? '-' }} | 
                                Semester: {{ $selectedSemName ?? '-' }} | 
                                Kelas: {{ $currentKelas->nama_kelas ?? '-' }} | 
                                Mata Pelajaran: {{ $currentMapel->nama_mata_pelajaran ?? '-' }}
                            </p>
                        </div>
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

                                            if (!function_exists('parseTpList')) {
                                                function parseTpList($value): array {
                                                    if (empty($value)) return [];

                                                    if (is_array($value)) {
                                                        $result = [];
                                                        foreach ($value as $item) {
                                                            $result = array_merge($result, parseTpList($item));
                                                        }
                                                        return array_values(array_unique(array_filter($result)));
                                                    }

                                                    if (is_string($value)) {
                                                        $value = trim($value);
                                                        if ($value === '') return [];

                                                        $decoded = json_decode($value, true);
                                                        if (json_last_error() === JSON_ERROR_NONE && !is_null($decoded) && $decoded !== $value) {
                                                            return parseTpList($decoded);
                                                        }

                                                        $cleaned = str_replace(['\"', '\\"'], '"', $value);
                                                        $cleaned = trim($cleaned, '[]"\'\\');
                                                        $cleaned = trim($cleaned);

                                                        if ($cleaned !== '') {
                                                            return [$cleaned];
                                                        }
                                                    }

                                                    return [];
                                                }
                                            }

                                            if (!function_exists('formatTpNumberedHtml')) {
                                                function formatTpNumberedHtml($value, $fallback = '-'): string {
                                                    $items = parseTpList($value);
                                                    if (empty($items)) {
                                                        return e($fallback);
                                                    }

                                                    $html = '<div class="d-flex flex-column gap-1 text-start">';
                                                    foreach ($items as $idx => $item) {
                                                        $html .= '<div>' . ($idx + 1) . '. ' . e($item) . '</div>';
                                                    }
                                                    $html .= '</div>';

                                                    return $html;
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
                                            <td class="text-start">{!! formatTpNumberedHtml($rec?->tp_optimal) !!}</td>
                                            <td class="text-start">{!! formatTpNumberedHtml($rec?->tp_perlu_peningkatan) !!}</td>
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

    <style>
        @media print {
            .header, #header, #sidebar, .sidebar, .pagetitle, .d-print-none,
            .dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate,
            footer, #footer, .footer {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            #main, .main {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                background: transparent !important;
            }
            .card-body {
                padding: 0 !important;
            }
            .table-responsive {
                overflow: visible !important;
            }
            .table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-top: 10px !important;
            }
            .table th, .table td {
                border: 1px solid #000000 !important;
                color: #000000 !important;
                padding: 6px 8px !important;
                font-size: 12px !important;
            }
            .table-light {
                background-color: #f2f2f2 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        let isUpdatingDropdowns = false;

        function syncKelasAndMapel(changedField) {
            if (isUpdatingDropdowns) return;
            isUpdatingDropdowns = true;

            const taId = $('#tahun_ajaran_id').val();
            const semName = $('#semester_name').val();
            const kelasId = $('#kelas_id').val();
            const mapelId = $('#mata_pelajaran_id').val();

            $.ajax({
                url: "{{ route('nilai.get-kelas-mapel') }}",
                type: "GET",
                data: {
                    tahun_ajaran_id: taId,
                    semester_name: semName,
                    kelas_id: changedField === 'kelas' ? kelasId : '',
                    mata_pelajaran_id: changedField === 'mapel' ? mapelId : ''
                },
                dataType: "json",
                success: function(res) {
                    if (changedField === 'ta_sem') {
                        const kSelect = $('#kelas_id');
                        const currK = kSelect.val();
                        kSelect.empty().append('<option value="" disabled selected></option>');
                        res.kelas.forEach(function(k) {
                            const sel = (currK == k.id) ? 'selected' : '';
                            kSelect.append(`<option value="${k.id}" ${sel}>${k.nama_kelas}</option>`);
                        });

                        const mSelect = $('#mata_pelajaran_id');
                        const currM = mSelect.val();
                        mSelect.empty().append('<option value="" disabled selected></option>');
                        res.mapels.forEach(function(m) {
                            const sel = (currM == m.id) ? 'selected' : '';
                            mSelect.append(`<option value="${m.id}" ${sel}>${m.nama_mata_pelajaran}</option>`);
                        });
                    } else if (changedField === 'kelas') {
                        const mSelect = $('#mata_pelajaran_id');
                        const currM = mSelect.val();
                        mSelect.empty().append('<option value="" disabled selected></option>');
                        res.mapels.forEach(function(m) {
                            const sel = (currM == m.id || res.mapels.length === 1) ? 'selected' : '';
                            mSelect.append(`<option value="${m.id}" ${sel}>${m.nama_mata_pelajaran}</option>`);
                        });
                    } else if (changedField === 'mapel') {
                        const kSelect = $('#kelas_id');
                        const currK = kSelect.val();
                        kSelect.empty().append('<option value="" disabled selected></option>');
                        res.kelas.forEach(function(k) {
                            const sel = (currK == k.id || res.kelas.length === 1) ? 'selected' : '';
                            kSelect.append(`<option value="${k.id}" ${sel}>${k.nama_kelas}</option>`);
                        });
                    }
                    isUpdatingDropdowns = false;
                },
                error: function() {
                    isUpdatingDropdowns = false;
                }
            });
        }

        $('#tahun_ajaran_id, #semester_name').on('change', function() {
            syncKelasAndMapel('ta_sem');
        });

        $('#kelas_id').on('change', function() {
            if (!isUpdatingDropdowns) {
                syncKelasAndMapel('kelas');
            }
        });

        $('#mata_pelajaran_id').on('change', function() {
            if (!isUpdatingDropdowns) {
                syncKelasAndMapel('mapel');
            }
        });

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
