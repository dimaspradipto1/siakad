@extends('layouts.dashboard.template')

@section('title', 'Input Nilai MID')

@section('content')
    <div class="pagetitle">
        <h1 class="text-primary fw-bold">Input Nilai MID</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('nilai.index') }}">Nilai</a></li>
                <li class="breadcrumb-item active">Nilai MID</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body pt-4">
                        <h5 class="card-title text-primary fw-bold mb-3 p-0">Form Filter Kelas & Mata Pelajaran</h5>
                        
                        <form action="{{ route('nilai.mid') }}" method="GET" class="row g-3">
                            <div class="col-md-3">
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
                            
                            <div class="col-md-3">
                                <label for="semester_id" class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                                <select name="semester_id" id="semester_id" class="form-select" required>
                                    <option value="" disabled selected>-- Pilih Semester --</option>
                                    @foreach($semesters as $sem)
                                        <option value="{{ $sem->id }}" {{ $selectedSem == $sem->id ? 'selected' : '' }}>
                                            {{ $sem->nama_semester }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
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

                            <div class="col-md-3">
                                <label for="mata_pelajaran_id" class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                                <select name="mata_pelajaran_id" id="mata_pelajaran_id" class="form-select" required>
                                    <option value="" disabled selected>-- Pilih Mata Pelajaran --</option>
                                    @foreach($mapels as $mp)
                                        <option value="{{ $mp->id }}" {{ $selectedMapel == $mp->id ? 'selected' : '' }}>
                                            {{ $mp->nama_mata_pelajaran }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                                <a href="{{ route('nilai.mid') }}" class="btn btn-secondary text-white btn-sm px-3" style="background-color: #6c757d; border-color: #6c757d;">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm px-3" style="background-color: #0d6efd; border-color: #0d6efd;">
                                    <i class="bi bi-search"></i> Get Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if($selectedTa && $selectedSem && $selectedKelas && $selectedMapel)
                <div class="card shadow-sm border-0">
                    <div class="card-body pt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="card-title text-primary fw-bold p-0 mb-0">Form Input Nilai MID (UTS)</h5>
                                @if($isLockedMid)
                                    <span class="badge bg-danger px-3 py-2" style="font-size:0.82rem;">🔒 Terkunci</span>
                                @else
                                    <span class="badge bg-success px-3 py-2" style="font-size:0.82rem;">🔓 Terbuka</span>
                                @endif
                            </div>
                            @if($isAdmin && $selectedMapel)
                                <form method="POST" action="{{ route('nilai.lock.toggle') }}" class="d-inline" onsubmit="return confirm('{{ $isLockedMid ? 'Buka kunci nilai MID ini?' : 'Kunci nilai MID ini? Guru tidak akan bisa mengubah nilai.' }}')">
                                    @csrf
                                    <input type="hidden" name="mata_pelajaran_id" value="{{ $selectedMapel }}">
                                    <input type="hidden" name="jenis" value="mid">
                                    <button type="submit" class="btn btn-sm px-3 fw-semibold {{ $isLockedMid ? 'btn-outline-success' : 'btn-outline-danger' }}">
                                        @if($isLockedMid)
                                            <i class="bi bi-unlock-fill me-1"></i> Buka Kunci
                                        @else
                                            <i class="bi bi-lock-fill me-1"></i> Kunci Nilai
                                        @endif
                                    </button>
                                </form>
                            @endif
                        </div>

                        @if(count($students) > 0)
                        <form action="{{ route('nilai.mid.save') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedTa }}">
                            <input type="hidden" name="semester_id" value="{{ $selectedSem }}">
                            <input type="hidden" name="kelas_id" value="{{ $selectedKelas }}">
                            <input type="hidden" name="mata_pelajaran_id" value="{{ $selectedMapel }}">

                            <div class="table-responsive col-lg-8 mx-auto">
                                <table class="table table-bordered table-hover align-middle text-center">
                                    <thead class="table-light fw-bold text-dark">
                                        <tr>
                                            <th style="width: 60px;">No</th>
                                            <th style="width: 150px;">NISN</th>
                                            <th class="text-start">Nama Siswa</th>
                                            <th style="width: 180px;">Nilai MID (UTS)</th>
                                            <th style="width: 180px;">Nilai Mid+</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $index => $siswa)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $siswa->nisn }}</td>
                                                <td class="text-start fw-semibold">{{ $siswa->nama_siswa }}</td>
                                                <td>
                                                    <input type="number" step="0.1" min="0" max="100" 
                                                        name="nilai[{{ $siswa->id }}][nilai_mid]" 
                                                        class="form-control text-center score-input" 
                                                        value="{{ $siswa->nilai_record && $siswa->nilai_record->nilai_mid !== null ? floatval($siswa->nilai_record->nilai_mid) : '' }}"
                                                        {{ $isLockedMid ? 'disabled' : '' }}>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.1" min="0" max="100" 
                                                        name="nilai[{{ $siswa->id }}][nilai_mid_plus]" 
                                                        class="form-control text-center score-input" 
                                                        value="{{ $siswa->nilai_record && $siswa->nilai_record->nilai_mid_plus !== null ? floatval($siswa->nilai_record->nilai_mid_plus) : '' }}"
                                                        {{ $isLockedMid ? 'disabled' : '' }}>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-3">
                                @if($isLockedMid)
                                    <div class="alert alert-warning py-2 px-3 mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-lock-fill"></i>
                                        <span>Nilai MID telah dikunci. Hubungi admin untuk membuka kunci.</span>
                                    </div>
                                @else
                                    <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan Nilai MID</button>
                                @endif
                            </div>
                        </form>
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
        .score-input {
            width: 80px;
            height: 30px;
            padding: 2px;
            font-size: 0.85rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            margin: 0 auto;
            display: inline-block;
        }
        .score-input::-webkit-outer-spin-button,
        .score-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .score-input {
            -moz-appearance: textfield;
        }
    </style>
@endsection
