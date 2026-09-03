@extends('layouts.dashboard.template')

@section('title', 'Tambah Pembagian Kelas')

@section('content')
    <div class="pagetitle">
        <h1>Tambah Data Pembagian Kelas</h1>
        <nav class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pembagiankelas.index') }}">Pembagian Kelas</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">

                        <form action="{{ route('pembagiankelas.store') }}" method="POST" id="formTambahPembagianKelas">
                            @csrf

                            {{-- 1. Tahun Ajaran --}}
                            <div class="mb-3">
                                <label for="tahun_ajaran_id" class="form-label fw-medium text-secondary">Tahun Ajaran</label>
                                <select id="tahun_ajaran_id" name="tahun_ajaran_id"
                                    class="form-select rounded-3 @error('tahun_ajaran_id') is-invalid @enderror" required>
                                    <option value="" disabled selected></option>
                                    @foreach($tahunAjarans as $ta)
                                        <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id') == $ta->id || (empty(old('tahun_ajaran_id')) && $ta->status == 'Aktif') ? 'selected' : '' }}>
                                            {{ $ta->nama_tahun_ajaran }} @if($ta->status == 'Aktif') (Aktif) @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('tahun_ajaran_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- 2. Tingkat --}}
                            <div class="mb-3">
                                <label for="tingkat" class="form-label fw-medium text-secondary">Tingkat</label>
                                <select id="tingkat" name="tingkat" class="form-select rounded-3">
                                    <option value="" disabled selected></option>
                                    @foreach(['1', '2', '3', '4', '5', '6'] as $t)
                                        <option value="{{ $t }}" {{ old('tingkat') == $t ? 'selected' : '' }}>Tingkat {{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 3. Kelas --}}
                            <div class="mb-3">
                                <label for="kelas_id" class="form-label fw-medium text-secondary">Kelas</label>
                                <select id="kelas_id" name="kelas_id"
                                    class="form-select rounded-3 @error('kelas_id') is-invalid @enderror" required>
                                    <option value="" disabled selected></option>
                                    @foreach($kelas as $k)
                                        @php
                                            $wali = $k->waliKelas->first()?->guru?->pegawai?->nama_pegawai ?? 'Belum ada wali kelas';
                                        @endphp
                                        <option value="{{ $k->id }}" data-tingkat="{{ $k->tingkat }}" data-walikelas="{{ $wali }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                            {{ $k->nama_kelas }} (Tingkat {{ $k->tingkat }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('kelas_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- 4. Nama Siswa --}}
                            <div class="mb-3">
                                <label for="siswa_id" class="form-label fw-medium text-secondary">Nama Siswa</label>
                                <select id="siswa_id" name="siswa_id"
                                    class="form-select select2 rounded-3 @error('siswa_id') is-invalid @enderror" required>
                                    <option value="" disabled selected>NISN + Nama Siswa</option>
                                    @foreach($siswas as $siswa)
                                        <option value="{{ $siswa->id }}" {{ old('siswa_id') == $siswa->id ? 'selected' : '' }}>
                                            {{ $siswa->nisn }} - {{ $siswa->nama_siswa }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('siswa_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- 5. Nama Walikelas (Readonly & Otomatis Terisi) --}}
                            <div class="mb-4">
                                <label for="nama_walikelas" class="form-label fw-medium text-secondary">Nama Walikelas</label>
                                <input type="text" id="nama_walikelas" class="form-control rounded-3 bg-white" 
                                    placeholder="otomatis terisi" readonly disabled>
                            </div>

                            <!-- ==========================================
                                 FOOTER BUTTONS
                                 ========================================== -->
                            <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top">
                                <a href="{{ route('pembagiankelas.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3">
                                    Batal
                                </a>
                                <button type="submit" class="btn btn-dark px-4 py-2 rounded-3">
                                    Simpan
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
            const waliKelasData = [
                @foreach($waliKelasList as $wk)
                    {
                        tahun_ajaran_id: "{{ $wk->tahun_ajaran_id }}",
                        kelas_id: "{{ $wk->kelas_id }}",
                        nama_walikelas: "{{ addslashes($wk->guru->pegawai->nama_pegawai ?? 'Belum ada wali kelas') }}"
                    },
                @endforeach
            ];

            function updateWaliKelas() {
                const selectedTa = $('#tahun_ajaran_id').val();
                const selectedKelas = $('#kelas_id').val();

                if (selectedTa && selectedKelas) {
                    const found = waliKelasData.find(item => item.tahun_ajaran_id == selectedTa && item.kelas_id == selectedKelas);
                    if (found) {
                        $('#nama_walikelas').val(found.nama_walikelas);
                        return;
                    }
                }

                const selectedOption = $('#kelas_id option:selected');
                const wali = selectedOption.data('walikelas');
                if (wali) {
                    $('#nama_walikelas').val(wali);
                } else {
                    $('#nama_walikelas').val('');
                }
            }

            function updateUnassignedStudents(selectedTa, currentSiswaId) {
                if (!selectedTa) return;
                
                $.ajax({
                    url: "{{ route('pembagiankelas.get-siswa') }}",
                    type: "GET",
                    data: {
                        tahun_ajaran_id: selectedTa,
                        current_siswa_id: currentSiswaId || ''
                    },
                    dataType: "json",
                    success: function(students) {
                        const siswaSelect = $('#siswa_id');
                        const prevVal = currentSiswaId || siswaSelect.val();
                        
                        siswaSelect.empty().append('<option value="" disabled selected>NISN + Nama Siswa</option>');
                        students.forEach(function(s) {
                            const isSelected = (prevVal == s.id) ? 'selected' : '';
                            siswaSelect.append(`<option value="${s.id}" ${isSelected}>${s.nisn} - ${s.nama_siswa}</option>`);
                        });
                        
                        if (siswaSelect.hasClass('select2-hidden-accessible')) {
                            siswaSelect.trigger('change.select2');
                        }
                    }
                });
            }

            $('#tahun_ajaran_id').on('change', function() {
                updateWaliKelas();
                updateUnassignedStudents($(this).val(), "{{ old('siswa_id') }}");
            });

            $('#kelas_id').on('change', function() {
                updateWaliKelas();
            });

            updateWaliKelas();

            // Filter Kelas berdasarkan Tingkat
            $('#tingkat').on('change', function() {
                const selectedTingkat = $(this).val();
                $('#kelas_id option').each(function() {
                    const optionTingkat = $(this).data('tingkat');
                    if (!selectedTingkat || !optionTingkat || optionTingkat == selectedTingkat) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
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
