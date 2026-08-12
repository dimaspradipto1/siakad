@extends('layouts.dashboard.template')

@section('title', 'Input Nilai Raport')

@section('content')
    <div class="pagetitle">
        <h1 class="text-primary fw-bold">Input Nilai Raport</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('nilai.index') }}">Nilai</a></li>
                <li class="breadcrumb-item active">Nilai Raport</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body pt-4">
                        <h5 class="card-title text-primary fw-bold mb-3 p-0">Form Filter Kelas & Mata Pelajaran</h5>
                        
                        <form action="{{ route('nilai.raport-input') }}" method="GET" class="row g-3">
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
                                <a href="{{ route('nilai.raport-input') }}" class="btn btn-secondary text-white btn-sm px-3" style="background-color: #6c757d; border-color: #6c757d;">
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
                                <h5 class="card-title text-primary fw-bold p-0 mb-0">Form Input Nilai Raport</h5>
                                @if($isLockedRaport)
                                    <span class="badge bg-danger px-3 py-2" style="font-size:0.82rem;">🔒 Terkunci</span>
                                @else
                                    <span class="badge bg-success px-3 py-2" style="font-size:0.82rem;">🔓 Terbuka</span>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                @if(!$isLockedRaport)
                                    <button type="button" class="btn btn-info btn-sm text-white px-3" id="btn-auto-calc"><i class="bi bi-cpu"></i> Isi Nilai Rapot dari Rata-rata</button>
                                @endif
                                @if($isAdmin && $selectedMapel)
                                    <form method="POST" action="{{ route('nilai.lock.toggle') }}" class="d-inline" onsubmit="return confirm('{{ $isLockedRaport ? 'Buka kunci nilai Raport ini?' : 'Kunci nilai Raport ini? Guru tidak akan bisa mengubah nilai.' }}')">
                                        @csrf
                                        <input type="hidden" name="mata_pelajaran_id" value="{{ $selectedMapel }}">
                                        <input type="hidden" name="jenis" value="raport">
                                        <button type="submit" class="btn btn-sm px-3 fw-semibold {{ $isLockedRaport ? 'btn-outline-success' : 'btn-outline-danger' }}">
                                            @if($isLockedRaport)
                                                <i class="bi bi-unlock-fill me-1"></i> Buka Kunci
                                            @else
                                                <i class="bi bi-lock-fill me-1"></i> Kunci Nilai
                                            @endif
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        @if(count($students) > 0)
                        <form action="{{ route('nilai.raport-input.save') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedTa }}">
                            <input type="hidden" name="semester_id" value="{{ $selectedSem }}">
                            <input type="hidden" name="kelas_id" value="{{ $selectedKelas }}">
                            <input type="hidden" name="mata_pelajaran_id" value="{{ $selectedMapel }}">

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle text-center" id="tabel-raport">
                                    <thead style="background-color:#1976d2; color:#fff;" class="fw-bold">
                                        <tr>
                                            <th style="width:45px;">No</th>
                                            <th class="text-start" style="min-width:140px;">Nama Siswa</th>
                                            <th style="width:110px;">NISN / NIS</th>
                                            <th style="width:100px;">Nilai</th>
                                            <th class="text-start" style="min-width:240px;">TP Yang diukur dan Tercapai dengan Optimal</th>
                                            <th class="text-start" style="min-width:240px;">TP yang diukur dan Perlu Peningkatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $index => $siswa)
                                            @php
                                                $rec    = $siswa->nilai_record;
                                                $rata2  = $siswa->nilai_rata2_calc;

                                                // Ambil TP yang sudah tersimpan (bisa JSON array atau null)
                                                $savedOptimal = [];
                                                $savedPeningkatan = [];
                                                if ($rec) {
                                                    $raw = $rec->tp_optimal;
                                                    if (is_array($raw)) $savedOptimal = $raw;
                                                    elseif (is_string($raw) && $raw !== '') {
                                                        $dec = json_decode($raw, true);
                                                        $savedOptimal = is_array($dec) ? $dec : [$raw];
                                                    }

                                                    $rawP = $rec->tp_perlu_peningkatan;
                                                    if (is_array($rawP)) $savedPeningkatan = $rawP;
                                                    elseif (is_string($rawP) && $rawP !== '') {
                                                        $decP = json_decode($rawP, true);
                                                        $savedPeningkatan = is_array($decP) ? $decP : [$rawP];
                                                    }
                                                }
                                            @endphp
                                            <tr data-siswa-id="{{ $siswa->id }}" data-rata2="{{ $rata2 ?? '' }}">
                                                <td>{{ $index + 1 }}</td>
                                                <td class="text-start fw-semibold">{{ $siswa->nama_siswa }}</td>
                                                <td class="small">
                                                    {{ $siswa->nisn }}<br>
                                                    <span class="text-muted">{{ $siswa->nis }}</span>
                                                </td>
                                                <td>
                                                    <input type="number" step="1" min="0" max="100"
                                                        name="nilai[{{ $siswa->id }}][nilai_raport]"
                                                        class="form-control text-center score-input raport-input"
                                                        value="{{ $rec && $rec->nilai_raport !== null ? intval($rec->nilai_raport) : '' }}"
                                                        {{ $isLockedRaport ? 'disabled' : '' }}>
                                                </td>

                                                {{-- TP Optimal: checkboxes --}}
                                                <td class="text-start px-2 py-2">
                                                    @if(count($tpOptimalOptions) > 0)
                                                        @foreach($tpOptimalOptions as $opt)
                                                            <div class="form-check mb-1">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="nilai[{{ $siswa->id }}][tp_optimal][]"
                                                                    value="{{ $opt }}"
                                                                    id="opt_{{ $siswa->id }}_{{ $loop->index }}"
                                                                    {{ in_array($opt, $savedOptimal) ? 'checked' : '' }}
                                                                    {{ $isLockedRaport ? 'disabled' : '' }}>
                                                                <label class="form-check-label small" for="opt_{{ $siswa->id }}_{{ $loop->index }}">
                                                                    {{ $opt }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted small fst-italic">Belum ada TP</span>
                                                    @endif
                                                </td>

                                                {{-- TP Peningkatan: checkboxes --}}
                                                <td class="text-start px-2 py-2">
                                                    @if(count($tpPeningkatanOptions) > 0)
                                                        @foreach($tpPeningkatanOptions as $opt)
                                                            <div class="form-check mb-1">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="nilai[{{ $siswa->id }}][tp_perlu_peningkatan][]"
                                                                    value="{{ $opt }}"
                                                                    id="pkt_{{ $siswa->id }}_{{ $loop->index }}"
                                                                    {{ in_array($opt, $savedPeningkatan) ? 'checked' : '' }}
                                                                    {{ $isLockedRaport ? 'disabled' : '' }}>
                                                                <label class="form-check-label small" for="pkt_{{ $siswa->id }}_{{ $loop->index }}">
                                                                    {{ $opt }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted small fst-italic">Belum ada TP</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-3">
                                @if($isLockedRaport)
                                    <div class="alert alert-warning py-2 px-3 mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-lock-fill"></i>
                                        <span>Nilai Raport telah dikunci. Hubungi admin untuk membuka kunci.</span>
                                    </div>
                                @else
                                    <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan Nilai Raport</button>
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
            width: 75px;
            height: 32px;
            padding: 2px 4px;
            font-size: 0.9rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            margin: 0 auto;
            display: block;
            text-align: center;
        }
        .score-input::-webkit-outer-spin-button,
        .score-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .score-input { -moz-appearance: textfield; }

        /* Tabel TP Checkbox styling */
        #tabel-raport td { vertical-align: top; padding: 8px 6px; }
        #tabel-raport .form-check { margin-bottom: 4px; }
        #tabel-raport .form-check-input { cursor: pointer; }
        #tabel-raport .form-check-label { cursor: pointer; line-height: 1.4; }
        #tabel-raport thead th { vertical-align: middle; text-align: center; }
        #tabel-raport tbody td:nth-child(2) { vertical-align: middle; }
        #tabel-raport tbody td:nth-child(4) { vertical-align: middle; }

        /* Stripe rows */
        #tabel-raport tbody tr:nth-child(even) { background-color: #f8f9ff; }
    </style>
@endsection

@push('script')
<script>
$(document).ready(function() {
    // Isi Nilai Rapot dari Nilai Rata2 yang sudah dihitung otomatis oleh sistem
    $('#btn-auto-calc').click(function() {
        $('tbody tr').each(function() {
            const rata2 = $(this).data('rata2');
            if (rata2 !== '' && rata2 !== undefined) {
                $(this).find('.raport-input').val(Math.round(parseFloat(rata2)));
            }
        });
    });
});
</script>
@endpush
