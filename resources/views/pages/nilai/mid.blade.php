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
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <h5 class="card-title text-primary fw-bold p-0 mb-0">Form Input Nilai MID (UTS)</h5>
                            @if($isLockedMid)
                                <span class="badge bg-danger px-3 py-2" style="font-size:0.82rem;">🔒 Terkunci</span>
                            @else
                                <span class="badge bg-success px-3 py-2" style="font-size:0.82rem;">🔓 Terbuka</span>
                            @endif
                            @if($selectedMapel)
                                <form id="form-lock-toggle" method="POST" action="{{ route('nilai.lock.toggle') }}" class="d-inline ms-1">
                                    @csrf
                                    <input type="hidden" name="mata_pelajaran_id" value="{{ $selectedMapel }}">
                                    <input type="hidden" name="jenis" value="mid">
                                    @if($isLockedMid)
                                        <button type="button" class="btn btn-sm px-3 fw-semibold btn-outline-success" style="border-radius: 6px;" onclick="handleUnlockNilai('Nilai MID')">
                                            <i class="bi bi-unlock-fill me-1"></i> Buka Kunci
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm px-3 fw-semibold btn-outline-danger" style="border-radius: 6px;" onclick="handleLockNilai('Nilai MID', 'form-nilai-mid')">
                                            <i class="bi bi-lock-fill me-1"></i> Kunci Nilai
                                        </button>
                                    @endif
                                </form>
                            @endif
                        </div>

                        @if(count($students) > 0)
                        <form id="form-nilai-mid" action="{{ route('nilai.mid.save') }}" method="POST">
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
                                            <th class="text-start px-3">Nama Siswa</th>
                                            <th style="width: 180px;">Nilai MID (UTS)</th>
                                            <th style="width: 180px;">Nilai Mid+</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $index => $siswa)
                                            <tr>
                                                <td class="align-middle fw-semibold">{{ $index + 1 }}</td>
                                                <td class="align-middle">{{ $siswa->nisn }}</td>
                                                <td class="text-start fw-bold align-middle px-3 text-dark">{{ $siswa->nama_siswa }}</td>
                                                <td class="align-middle">
                                                    <input type="number" step="0.1" min="0" max="100" 
                                                        name="nilai[{{ $siswa->id }}][nilai_mid]" 
                                                        class="form-control form-control-sm text-center score-input mx-auto fw-bold" 
                                                        value="{{ $siswa->nilai_record && $siswa->nilai_record->nilai_mid !== null ? floatval($siswa->nilai_record->nilai_mid) : '' }}"
                                                        {{ $isLockedMid ? 'disabled' : '' }}>
                                                </td>
                                                <td class="align-middle">
                                                    <input type="number" step="0.1" min="0" max="100" 
                                                        name="nilai[{{ $siswa->id }}][nilai_mid_plus]" 
                                                        class="form-control form-control-sm text-center score-input mx-auto fw-bold" 
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
                                        <span>Nilai MID telah dikunci. Buka kunci nilai jika ingin melakukan pengeditan.</span>
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

@push('script')
<script>
function handleLockNilai(jenisName, formId) {
    Swal.fire({
        title: 'Kunci ' + jenisName + '?',
        html: 'Nilai yang Anda masukkan akan <strong>disimpan dan langsung dikunci</strong>.<br><small class="text-muted">Setelah dikunci, nilai tidak dapat diubah kembali sampai dibuka kuncinya.</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-lock-fill me-1"></i> Ya, Simpan & Kunci',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const formNilai = document.getElementById(formId);
            if (formNilai) {
                let lockInput = document.getElementById('input-auto-lock');
                if (!lockInput) {
                    lockInput = document.createElement('input');
                    lockInput.type = 'hidden';
                    lockInput.id = 'input-auto-lock';
                    lockInput.name = 'auto_lock';
                    formNilai.appendChild(lockInput);
                }
                lockInput.value = '1';
                formNilai.submit();
            } else {
                document.getElementById('form-lock-toggle').submit();
            }
        }
    });
}

function handleUnlockNilai(jenisName) {
    Swal.fire({
        title: 'Buka Kunci ' + jenisName + '?',
        text: 'Kunci nilai akan dibuka agar Anda dapat mengedit kembali data nilai.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-unlock-fill me-1"></i> Ya, Buka Kunci',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('form-lock-toggle').submit();
        }
    });
}
</script>
@endpush
