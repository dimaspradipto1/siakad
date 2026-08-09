@extends('layouts.dashboard.template')

@section('title', 'Edit Data Mata Pelajaran Aktif')

@section('content')
    <div class="pagetitle">
        <h1>Edit Data Mata Pelajaran Aktif</h1>
        <nav class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('matapelajaranaktif.index') }}">Mata Pelajaran Aktif</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">

                        <form action="{{ route('matapelajaranaktif.update', $matapelajaran->id) }}" method="POST" id="formEditMapelAktif">
                            @csrf
                            @method('PUT')

                            {{-- Row 1: Kelas, Tahun Ajaran, Semester --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="kelas_id" class="form-label fw-medium text-secondary">Kelas</label>
                                    <select id="kelas_id" name="kelas_id" 
                                        class="form-select rounded-3 @error('kelas_id') is-invalid @enderror" required>
                                        <option value="" disabled></option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id }}" {{ old('kelas_id', $matapelajaran->kelas_id) == $k->id ? 'selected' : '' }}>
                                                {{ $k->nama_kelas }} (Tingkat {{ $k->tingkat }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="tahun_ajaran_id" class="form-label fw-medium text-secondary">Tahun Ajaran</label>
                                    <select id="tahun_ajaran_id" name="tahun_ajaran_id" 
                                        class="form-select rounded-3 @error('tahun_ajaran_id') is-invalid @enderror" required>
                                        <option value="" disabled></option>
                                        @foreach($tahunAjarans as $ta)
                                            <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id', $matapelajaran->tahun_ajaran_id) == $ta->id ? 'selected' : '' }}>
                                                {{ $ta->nama_tahun_ajaran }} @if($ta->status == 'Aktif') (Aktif) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tahun_ajaran_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="semester_id" class="form-label fw-medium text-secondary">Semester</label>
                                    <select id="semester_id" name="semester_id" 
                                        class="form-select rounded-3 @error('semester_id') is-invalid @enderror" required>
                                        <option value="" disabled>-- Pilih Semester --</option>
                                        @foreach($semesters as $s)
                                            <option value="{{ $s->id }}" data-tahun-ajaran="{{ $s->tahun_ajaran_id }}" {{ old('semester_id', $matapelajaran->semester_id) == $s->id ? 'selected' : '' }}>
                                                {{ $s->nama_semester }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('semester_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Row 2: Nama Mata Pelajaran, Nama Guru, Status --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="master_mapel_id" class="form-label fw-medium text-secondary">Nama Mata Pelajaran</label>
                                    <select id="master_mapel_id" name="master_mapel_id" 
                                        class="form-select rounded-3 @error('master_mapel_id') is-invalid @enderror" required>
                                        <option value="" disabled></option>
                                        @foreach($masterMapels as $mp)
                                            <option value="{{ $mp->id ?? $mp->kode_mapel }}" {{ old('master_mapel_id', $matapelajaran->kode_mapel) == ($mp->id ?? $mp->kode_mapel) || old('master_mapel_id', $matapelajaran->nama_mata_pelajaran) == $mp->nama_mata_pelajaran ? 'selected' : '' }}>
                                                {{ $mp->nama_mata_pelajaran }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('master_mapel_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="guru_id" class="form-label fw-medium text-secondary">Nama Guru</label>
                                    <select id="guru_id" name="guru_id" 
                                        class="form-select rounded-3 @error('guru_id') is-invalid @enderror" required>
                                        <option value="" disabled></option>
                                        @foreach($gurus as $g)
                                            <option value="{{ $g->id }}" {{ old('guru_id', $matapelajaran->guru_id) == $g->id ? 'selected' : '' }}>
                                                {{ $g->pegawai?->nama_pegawai ?? '-' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('guru_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="status" class="form-label fw-medium text-secondary">Status</label>
                                    <select id="status" name="status" 
                                        class="form-select rounded-3 @error('status') is-invalid @enderror" required>
                                        <option value="Aktif" {{ old('status', $matapelajaran->status ?? 'Aktif') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Tidak Aktif" {{ old('status', $matapelajaran->status) == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Row 3: Hari Mengajar, Jam Mengajar --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label for="hari_mengajar" class="form-label fw-medium text-secondary">Hari Mengajar</label>
                                    <input type="text" id="hari_mengajar" name="hari_mengajar" 
                                        class="form-control rounded-3 @error('hari_mengajar') is-invalid @enderror" 
                                        value="{{ old('hari_mengajar', $matapelajaran->hari_mengajar) }}" required placeholder="Contoh: Senin">
                                    @error('hari_mengajar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="jam_mengajar" class="form-label fw-medium text-secondary">Jam Mengajar</label>
                                    <input type="text" id="jam_mengajar" name="jam_mengajar" 
                                        class="form-control rounded-3 @error('jam_mengajar') is-invalid @enderror" 
                                        value="{{ old('jam_mengajar', $matapelajaran->jam_mengajar) }}" required placeholder="Contoh: 07:30 - 09:00">
                                    @error('jam_mengajar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <!-- ==========================================
                                 FOOTER BUTTONS
                                 ========================================== -->
                            <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top">
                                <a href="{{ route('matapelajaranaktif.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3">
                                    Batal
                                </a>
                                <button type="submit" class="btn btn-dark px-4 py-2 rounded-3">
                                    Simpan Perubahan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            function filterSemesters() {
                var selectedTaId = $('#tahun_ajaran_id').val();
                $('#semester_id option').each(function() {
                    var taId = $(this).attr('data-tahun-ajaran');
                    if (!taId) return;
                    if (selectedTaId && taId == selectedTaId) {
                        $(this).show().prop('disabled', false);
                    } else {
                        $(this).hide().prop('disabled', true);
                    }
                });

                var selectedOpt = $('#semester_id option:selected');
                if (selectedOpt.length && selectedOpt.is(':disabled')) {
                    var firstValid = $('#semester_id option:not(:disabled):first').val();
                    $('#semester_id').val(firstValid || '');
                }
            }

            $('#tahun_ajaran_id').on('change', function() {
                filterSemesters();
            });

            filterSemesters();
        });

        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan!',
                html: `<ul class="text-start ps-3 mb-0">{!! implode('', $errors->all('<li>:message</li>')) !!}</ul>`,
                confirmButtonColor: '#d33',
            });
        @endif
    </script>
@endpush
