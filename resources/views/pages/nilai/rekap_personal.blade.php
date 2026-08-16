@extends('layouts.dashboard.template')

@section('title', 'Rekap Nilai Siswa')

@section('content')
    <div class="pagetitle">
        <h1 class="text-primary fw-bold">Rekap Nilai Siswa</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Rekap Nilai</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        <h5 class="card-title text-dark fw-bold mb-4 p-0">Form Rekap Nilai Siswa</h5>
                        
                        <form action="{{ route('nilai.index') }}" method="GET" class="row g-4">
                            @php
                                $hasMultipleChildren = isset($children) && $children->count() > 1;
                            @endphp

                            @if($hasMultipleChildren)
                            <div class="col-md-12">
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
                                    @if(isset($semesters))
                                        @foreach($semesters as $sem)
                                            <option value="{{ $sem->nama_semester }}" {{ $selectedSemName == $sem->nama_semester ? 'selected' : '' }}>
                                                {{ $sem->nama_semester }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="kelas_id" class="form-label fw-semibold text-dark">Kelas</label>
                                <select name="kelas_id" id="kelas_id" class="form-select py-2 bg-light" style="border-radius: 8px;" readonly required>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" selected>{{ $k->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="mata_pelajaran_id" class="form-label fw-semibold text-dark">Mata Pelajaran</label>
                                <select name="mata_pelajaran_id" id="mata_pelajaran_id" class="form-select py-2" style="border-radius: 8px;" required>
                                    <option value="" disabled selected></option>
                                    @foreach($mapels as $mp)
                                        <option value="{{ $mp->id }}" {{ (string) $selectedMapel === (string) $mp->id ? 'selected' : '' }}>
                                            {{ $mp->nama_mata_pelajaran }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 d-flex justify-content-end align-items-center gap-4 pt-2">
                                <a href="{{ route('nilai.index') }}" class="text-dark fw-bold text-decoration-none small" style="font-size: 0.95rem;">
                                    Reset
                                </a>
                                <button type="submit" class="btn btn-dark px-4 py-2" style="background-color: #212529; border-color: #212529; border-radius: 8px; font-weight: bold; font-size: 0.95rem;">
                                    Tampilkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if($selectedTa && $selectedSem && $selectedMapel)
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        <div class="table-responsive">
                            <table class="table align-middle text-center table-bordered">
                                <thead class="table-light fw-bold text-dark">
                                    <tr>
                                        <th>NISN</th>
                                        <th>Nama Siswa</th>
                                        <th>Nilai Harian</th>
                                        <th>Nilai MID+</th>
                                        <th>Nilai PAS+</th>
                                        <th>Nilai Akhir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $harian = $gradeRecord && $gradeRecord->nilai_harian !== null ? floatval($gradeRecord->nilai_harian) : null;
                                        $midPlus = $gradeRecord && $gradeRecord->nilai_mid_plus !== null ? floatval($gradeRecord->nilai_mid_plus) : ($gradeRecord && $gradeRecord->nilai_mid !== null ? floatval($gradeRecord->nilai_mid) : null);
                                        $pasPlus = $gradeRecord && $gradeRecord->nilai_pas_plus !== null ? floatval($gradeRecord->nilai_pas_plus) : ($gradeRecord && $gradeRecord->nilai_pas !== null ? floatval($gradeRecord->nilai_pas) : null);
                                        $akhir = $gradeRecord && $gradeRecord->nilai_raport !== null ? floatval($gradeRecord->nilai_raport) : null;

                                        if (!function_exists('formatValPersonal')) {
                                            function formatValPersonal($val) {
                                                if ($val === null) return '-';
                                                return $val == intval($val) ? intval($val) : number_format($val, 1);
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td class="fw-bold">{{ $siswa->nisn }}</td>
                                        <td class="fw-semibold text-start">{{ $siswa->nama_siswa }}</td>
                                        <td>{{ formatValPersonal($harian) }}</td>
                                        <td>{{ formatValPersonal($midPlus) }}</td>
                                        <td>{{ formatValPersonal($pasPlus) }}</td>
                                        <td class="fw-bold text-primary">{{ formatValPersonal($akhir) }}</td>
                                    </tr>
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
