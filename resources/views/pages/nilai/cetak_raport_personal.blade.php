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
if (!function_exists('terbilang')) {
    function terbilang($number) {
        $number = abs($number);
        $words = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $temp = "";
        if ($number < 12) {
            $temp = " " . $words[$number];
        } else if ($number < 20) {
            $temp = terbilang($number - 10) . " belas";
        } else if ($number < 100) {
            $temp = terbilang((int)($number / 10)) . " puluh" . terbilang($number % 10);
        } else if ($number < 200) {
            $temp = " seratus" . terbilang($number - 100);
        }
        return trim($temp);
    }
}
@endphp

@section('title', 'Cetak Raport')

@section('content')
    <div class="pagetitle">
        <h1 class="text-primary fw-bold">Cetak Raport</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Cetak Raport</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        <h5 class="card-title text-dark fw-bold mb-4 p-0">Form Cetak Raport Siswa</h5>
                        
                        <form action="{{ route('nilai.raport.personal') }}" method="GET" class="row g-4">
                            @php
                                $hasMultipleChildren = isset($children) && $children->count() > 1;
                                $colClass = $hasMultipleChildren ? 'col-md-3' : 'col-md-4';
                            @endphp

                            @if($hasMultipleChildren)
                            <div class="col-md-3">
                                <label for="child_id" class="form-label fw-semibold text-dark">Nama Anak <span class="text-danger">*</span></label>
                                <select name="child_id" id="child_id" class="form-select py-2" style="border-radius: 8px;" onchange="this.form.submit()">
                                    @foreach($children as $c)
                                        @php
                                            $cPk = $c->pembagianKelas->firstWhere('tahun_ajaran_id', $selectedTa);
                                            $cKNama = $cPk?->kelas?->nama_kelas ?? ($c->kelas?->nama_kelas ?? '-');
                                        @endphp
                                        <option value="{{ $c->id }}" {{ $siswa->id === $c->id ? 'selected' : '' }}>
                                            {{ $c->nama_siswa }} (Kelas {{ $cKNama }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div class="{{ $colClass }}">
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
                            
                            <div class="{{ $colClass }}">
                                <label for="semester_name" class="form-label fw-semibold text-dark">Semester <span class="text-danger">*</span></label>
                                <select name="semester_name" id="semester_name" class="form-select py-2" style="border-radius: 8px;" required>
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

                            <div class="{{ $colClass }}">
                                <label for="kelas_id" class="form-label fw-semibold text-dark">Kelas <span class="text-danger">*</span></label>
                                <select name="kelas_id" id="kelas_id" class="form-select py-2 bg-light" style="border-radius: 8px;" readonly required>
                                    <option value="{{ $kelasModel->id ?? '' }}" selected>{{ $kelasModel->nama_kelas ?? '-' }}</option>
                                </select>
                            </div>

                            <div class="col-12 d-flex justify-content-end align-items-center gap-4 pt-2">
                                <a href="{{ route('nilai.raport.personal') }}" class="text-dark fw-bold text-decoration-none small" style="font-size: 0.95rem;">
                                    Reset
                                </a>
                                <button type="submit" class="btn btn-dark px-4 py-2" style="background-color: #212529; border-color: #212529; border-radius: 8px; font-weight: bold; font-size: 0.95rem;">
                                    Tampilkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if($selectedTa && $selectedSem)
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        @if(count($classMapels) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle text-center">
                                <thead class="table-light fw-bold text-dark">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th style="width: 200px;">Mata Pelajaran</th>
                                        <th style="width: 80px;">KKM</th>
                                        <th style="width: 80px;">Angka</th>
                                        <th>Huruf</th>
                                        <th style="width: 100px;">Keterangan</th>
                                        <th>TP Yang Diukur dan Tercapai Optimal</th>
                                        <th>TP Yang Perlu Peningkatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalNilai = 0;
                                        $countNilai = 0;
                                    @endphp
                                    @foreach($classMapels as $index => $mp)
                                        @php
                                            $rec = $grades[$mp->id] ?? null;
                                            $nilai = $rec && $rec->nilai_raport !== null ? intval($rec->nilai_raport) : null;
                                            $predikat = $rec && $rec->predikat ? $rec->predikat : null;
                                            
                                            if ($nilai !== null) {
                                                $totalNilai += $nilai;
                                                $countNilai++;
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

                                            if (!function_exists('formatTpNumberedText')) {
                                                function formatTpNumberedText($value, $fallback = '-'): string {
                                                    $items = parseTpList($value);
                                                    if (empty($items)) {
                                                        return $fallback;
                                                    }

                                                    $lines = [];
                                                    foreach ($items as $idx => $item) {
                                                        $lines[] = ($idx + 1) . '. ' . e($item);
                                                    }

                                                    return implode('<br>', $lines);
                                                }
                                            }

                                            $tpOptimalSiswa = $rec->tp_optimal ?? null;
                                            $tpPeningkatanSiswa = $rec->tp_perlu_peningkatan ?? null;

                                            $rawOpt = $tpOptimalSiswa ?: $mp->tp_optimal;
                                            $rawImp = $tpPeningkatanSiswa ?: $mp->tp_peningkatan;

                                            $optText = formatTpNumberedText($rawOpt, 'Menunjukkan penguasaan kompetensi dengan sangat baik.');
                                            $impText = formatTpNumberedText($rawImp, 'Perlu bimbingan lebih lanjut untuk meningkatkan kompetensi.');
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td class="text-start fw-semibold">{{ $mp->nama_mata_pelajaran }}</td>
                                            <td>{{ $mp->kkm ?? 75 }}</td>
                                            <td class="fw-bold">{{ $nilai !== null ? $nilai : '-' }}</td>
                                            <td style="text-transform: capitalize;">
                                                {{ $nilai !== null ? terbilang($nilai) : '-' }}
                                            </td>
                                            <td class="fw-bold">{{ $predikat !== null ? $predikat : '-' }}</td>
                                            <td class="text-start" style="font-size: 0.85rem;">{!! $optText !!}</td>
                                            <td class="text-start" style="font-size: 0.85rem;">{!! $impText !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @php
                            $rataRata = $countNilai > 0 ? round($totalNilai / $countNilai, 1) : 0;
                        @endphp

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <table class="table table-borderless align-middle fw-bold">
                                    <tr>
                                        <td style="width: 180px;">Jumlah Nilai</td>
                                        <td style="width: 20px;">:</td>
                                        <td>{{ $totalNilai > 0 ? $totalNilai : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Rata-Rata</td>
                                        <td>:</td>
                                        <td>{{ $rataRata > 0 ? $rataRata : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Rangking</td>
                                        <td>:</td>
                                        <td>{{ $ranking }} dari {{ $totalStudents }} siswa</td>
                                    </tr>
                                    <tr>
                                        <td>Ekstrakurikuler</td>
                                        <td>:</td>
                                        <td>{{ $ekskulText }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end align-items-end pb-3">
                                <a href="{{ route('nilai.cetak-raport.print', ['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $selectedTa, 'semester_id' => $selectedSem]) }}" target="_blank" class="btn btn-dark px-4 py-2" style="background-color: #212529; border-color: #212529; border-radius: 8px; font-weight: bold;">
                                    Cetak
                                </a>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-warning text-center my-3"><i class="bi bi-exclamation-triangle-fill"></i> Tidak ada data nilai raport untuk periode terpilih.</div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>
@endsection
