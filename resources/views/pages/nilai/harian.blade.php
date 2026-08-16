@extends('layouts.dashboard.template')

@section('title', 'Input Nilai Harian')

@section('content')
    <div class="pagetitle">
        <h1 class="text-primary fw-bold">Input Nilai Harian</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('nilai.index') }}">Nilai</a></li>
                <li class="breadcrumb-item active">Nilai Harian</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body pt-4">
                        <h5 class="card-title text-primary fw-bold mb-3 p-0">Form Filter Kelas & Mata Pelajaran</h5>
                        
                        <form action="{{ route('nilai.harian') }}" method="GET" class="row g-3">
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
                                <a href="{{ route('nilai.harian') }}" class="btn btn-secondary text-white btn-sm px-3" style="background-color: #6c757d; border-color: #6c757d;">
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
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="card-title text-primary fw-bold p-0 mb-0">Form Input Nilai Harian</h5>
                                @if($isLockedHarian)
                                    <span class="badge bg-danger px-3 py-2" style="font-size:0.82rem;">🔒 Terkunci</span>
                                @else
                                    <span class="badge bg-success px-3 py-2" style="font-size:0.82rem;">🔓 Terbuka</span>
                                @endif
                                @if($selectedMapel)
                                    <form id="form-lock-toggle" method="POST" action="{{ route('nilai.lock.toggle') }}" class="d-inline ms-1">
                                        @csrf
                                        <input type="hidden" name="mata_pelajaran_id" value="{{ $selectedMapel }}">
                                        <input type="hidden" name="jenis" value="harian">
                                        @if($isLockedHarian)
                                            <button type="button" class="btn btn-sm px-3 fw-semibold btn-outline-success" style="border-radius: 6px;" onclick="handleUnlockNilai('Nilai Harian')">
                                                <i class="bi bi-unlock-fill me-1"></i> Buka Kunci
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm px-3 fw-semibold btn-outline-danger" style="border-radius: 6px;" onclick="handleLockNilai('Nilai Harian', 'form-nilai-harian')">
                                                <i class="bi bi-lock-fill me-1"></i> Kunci Nilai
                                            </button>
                                        @endif
                                    </form>
                                @endif
                            </div>
                        </div>

                        @if(count($students) > 0)
                        <form id="form-nilai-harian" action="{{ route('nilai.harian.save') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedTa }}">
                            <input type="hidden" name="semester_id" value="{{ $selectedSem }}">
                            <input type="hidden" name="kelas_id" value="{{ $selectedKelas }}">
                            <input type="hidden" name="mata_pelajaran_id" value="{{ $selectedMapel }}">

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle text-center" style="font-size: 0.85rem; min-width: 1200px;">
                                    <thead class="table-light fw-bold text-dark">
                                        <tr>
                                            <th rowspan="2" class="align-middle" style="width: 40px;">No</th>
                                            <th rowspan="2" class="align-middle" style="width: 100px;">NISN</th>
                                            <th rowspan="2" class="align-middle text-start" style="width: 200px;">Nama Siswa</th>
                                            <th colspan="4" class="align-middle">Lingkup Materi 1</th>
                                            <th colspan="4" class="align-middle">Lingkup Materi 2</th>
                                            <th colspan="4" class="align-middle">Lingkup Materi 3</th>
                                            <th colspan="4" class="align-middle">Lingkup Materi 4</th>
                                            <th colspan="4" class="align-middle">Lingkup Materi 5</th>
                                            <th colspan="5" class="align-middle text-primary">Sumatif LM</th>
                                            <th rowspan="2" class="align-middle text-success" style="width: 90px;">Sumatif Akhir</th>
                                        </tr>
                                        <tr>
                                            <!-- LM1 -->
                                            <th style="width: 45px;">TP1</th><th style="width: 45px;">TP2</th><th style="width: 45px;">TP3</th><th style="width: 45px;">TP4</th>
                                            <!-- LM2 -->
                                            <th style="width: 45px;">TP1</th><th style="width: 45px;">TP2</th><th style="width: 45px;">TP3</th><th style="width: 45px;">TP4</th>
                                            <!-- LM3 -->
                                            <th style="width: 45px;">TP1</th><th style="width: 45px;">TP2</th><th style="width: 45px;">TP3</th><th style="width: 45px;">TP4</th>
                                            <!-- LM4 -->
                                            <th style="width: 45px;">TP1</th><th style="width: 45px;">TP2</th><th style="width: 45px;">TP3</th><th style="width: 45px;">TP4</th>
                                            <!-- LM5 -->
                                            <th style="width: 45px;">TP1</th><th style="width: 45px;">TP2</th><th style="width: 45px;">TP3</th><th style="width: 45px;">TP4</th>
                                            <!-- Sumatif LM -->
                                            <th class="text-primary" style="width: 45px;">1</th>
                                            <th class="text-primary" style="width: 45px;">2</th>
                                            <th class="text-primary" style="width: 45px;">3</th>
                                            <th class="text-primary" style="width: 45px;">4</th>
                                            <th class="text-primary" style="width: 45px;">5</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $index => $siswa)
                                            @php
                                                $rec = $siswa->nilai_record;
                                            @endphp
                                            <tr data-siswa-id="{{ $siswa->id }}">
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $siswa->nisn }}</td>
                                                <td class="text-start fw-semibold">{{ $siswa->nama_siswa }}</td>
                                                
                                                <!-- LM1 inputs -->
                                                @for($tp=1; $tp<=4; $tp++)
                                                    <td>
                                                        <input type="number" step="0.1" min="0" max="100" 
                                                            name="nilai[{{ $siswa->id }}][lm1_tp{{ $tp }}]" 
                                                            class="form-control form-control-sm text-center tp-input" 
                                                            data-lm="1" data-tp="{{ $tp }}"
                                                            value="{{ $rec && $rec->{'lm1_tp'.$tp} !== null ? floatval($rec->{'lm1_tp'.$tp}) : '' }}"
                                                            {{ $isLockedHarian ? 'disabled' : '' }}>
                                                    </td>
                                                @endfor

                                                <!-- LM2 inputs -->
                                                @for($tp=1; $tp<=4; $tp++)
                                                    <td>
                                                        <input type="number" step="0.1" min="0" max="100" 
                                                            name="nilai[{{ $siswa->id }}][lm2_tp{{ $tp }}]" 
                                                            class="form-control form-control-sm text-center tp-input" 
                                                            data-lm="2" data-tp="{{ $tp }}"
                                                            value="{{ $rec && $rec->{'lm2_tp'.$tp} !== null ? floatval($rec->{'lm2_tp'.$tp}) : '' }}"
                                                            {{ $isLockedHarian ? 'disabled' : '' }}>
                                                    </td>
                                                @endfor

                                                <!-- LM3 inputs -->
                                                @for($tp=1; $tp<=4; $tp++)
                                                    <td>
                                                        <input type="number" step="0.1" min="0" max="100" 
                                                            name="nilai[{{ $siswa->id }}][lm3_tp{{ $tp }}]" 
                                                            class="form-control form-control-sm text-center tp-input" 
                                                            data-lm="3" data-tp="{{ $tp }}"
                                                            value="{{ $rec && $rec->{'lm3_tp'.$tp} !== null ? floatval($rec->{'lm3_tp'.$tp}) : '' }}"
                                                            {{ $isLockedHarian ? 'disabled' : '' }}>
                                                    </td>
                                                @endfor

                                                <!-- LM4 inputs -->
                                                @for($tp=1; $tp<=4; $tp++)
                                                    <td>
                                                        <input type="number" step="0.1" min="0" max="100" 
                                                            name="nilai[{{ $siswa->id }}][lm4_tp{{ $tp }}]" 
                                                            class="form-control form-control-sm text-center tp-input" 
                                                            data-lm="4" data-tp="{{ $tp }}"
                                                            value="{{ $rec && $rec->{'lm4_tp'.$tp} !== null ? floatval($rec->{'lm4_tp'.$tp}) : '' }}"
                                                            {{ $isLockedHarian ? 'disabled' : '' }}>
                                                    </td>
                                                @endfor

                                                <!-- LM5 inputs -->
                                                @for($tp=1; $tp<=4; $tp++)
                                                    <td>
                                                        <input type="number" step="0.1" min="0" max="100" 
                                                            name="nilai[{{ $siswa->id }}][lm5_tp{{ $tp }}]" 
                                                            class="form-control form-control-sm text-center tp-input" 
                                                            data-lm="5" data-tp="{{ $tp }}"
                                                            value="{{ $rec && $rec->{'lm5_tp'.$tp} !== null ? floatval($rec->{'lm5_tp'.$tp}) : '' }}"
                                                            {{ $isLockedHarian ? 'disabled' : '' }}>
                                                    </td>
                                                @endfor

                                                <!-- Sumatif LM displays -->
                                                @for($lm=1; $lm<=5; $lm++)
                                                    <td class="fw-bold text-primary lm-avg" data-lm="{{ $lm }}">
                                                        {{ $rec && $rec->{'lm'.$lm} !== null ? number_format($rec->{'lm'.$lm}, 1) : '.....' }}
                                                    </td>
                                                @endfor

                                                <!-- Sumatif Akhir display -->
                                                <td class="fw-bold text-success sumatif-akhir">
                                                    {{ $rec && $rec->nilai_harian !== null ? number_format($rec->nilai_harian, 1) : '.....' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-3">
                                @if($isLockedHarian)
                                    <div class="alert alert-warning py-2 px-3 mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-lock-fill"></i>
                                        <span>Nilai Harian telah dikunci. Hubungi admin untuk membuka kunci.</span>
                                    </div>
                                @else
                                    <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan Nilai Harian</button>
                                @endif
                            </div>
                        </form>
                        @else
                        <div class="alert alert-warning text-center my-3"><i class="bi bi-exclamation-triangle-fill"></i> Tidak ada data siswa ditemukan untuk kelas ini di tahun ajaran tersebut.</div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>

    <style>
        /* Compact Spreadsheet input styles */
        .tp-input {
            width: 44px;
            height: 28px;
            padding: 2px;
            font-size: 0.8rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            margin: 0 auto;
            display: inline-block;
        }
        .tp-input:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
        }
        /* Hide spinner arrows for number input */
        .tp-input::-webkit-outer-spin-button,
        .tp-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .tp-input {
            -moz-appearance: textfield;
        }
    </style>
@endsection

@push('script')
<script>
$(document).ready(function() {
    // Recalculate average on input change
    $('.tp-input').on('input', function() {
        const tr = $(this).closest('tr');
        const siswaId = tr.data('siswa-id');
        const lm = $(this).data('lm');

        // 1. Calculate LM average
        let sum = 0;
        let count = 0;
        
        tr.find(`.tp-input[data-lm="${lm}"]`).each(function() {
            const val = $(this).val();
            if (val !== '' && !isNaN(val)) {
                sum += parseFloat(val);
                count++;
            }
        });

        const lmAvgCell = tr.find(`.lm-avg[data-lm="${lm}"]`);
        let lmAverage = null;
        
        if (count > 0) {
            lmAverage = sum / count;
            lmAvgCell.text(lmAverage.toFixed(1));
        } else {
            lmAvgCell.text('.....');
        }

        // 2. Calculate Sumatif Akhir (if all 4 are computed)
        let lmsFilled = true;
        let lmsSum = 0;
        
        tr.find('.lm-avg').each(function() {
            const val = $(this).text().trim();
            if (val === '.....' || val === '') {
                lmsFilled = false;
            } else {
                lmsSum += parseFloat(val);
            }
        });

        const sumatifCell = tr.find('.sumatif-akhir');
        if (lmsFilled) {
            const finalScore = lmsSum / 5;
            sumatifCell.text(finalScore.toFixed(1));
        } else {
            sumatifCell.text('.....');
        }
    });

    // Run trigger on page load to ensure everything is calculated if there are values pre-filled
    $('.tp-input').trigger('input');
});

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
