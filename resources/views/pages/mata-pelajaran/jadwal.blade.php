@extends('layouts.dashboard.template')

@section('title', 'Jadwal Mata Pelajaran')

@section('content')
    <div class="pagetitle">
        <h1 class="text-primary fw-bold">Jadwal Mata Pelajaran</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Jadwal Mata Pelajaran</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        <div class="d-flex justify-content-between align-items-center mb-4 p-0">
                            <h5 class="card-title text-dark fw-bold mb-0 p-0">Daftar Jadwal Mata Pelajaran</h5>
                            @if(auth()->user()->roles === 'admin')
                            <a href="{{ route('matapelajaran.create') }}" class="btn btn-dark px-4 py-2 fw-bold" style="border-radius: 8px;">
                                Tambah Data Mapel Aktif
                            </a>
                            @endif
                        </div>
                        
                        <form action="{{ route('matapelajaran.jadwal') }}" method="GET" class="row g-4">
                            <div class="col-md-4">
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
                            
                            <div class="col-md-4">
                                <label for="semester_name" class="form-label fw-semibold text-dark">Semester</label>
                                <select name="semester_name" id="semester_name" class="form-select py-2" style="border-radius: 8px;" required>
                                    <option value="" disabled selected></option>
                                    <option value="Semester 1 (Ganjil)" {{ $selectedSemName == 'Semester 1 (Ganjil)' ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                                    <option value="Semester 2 (Genap)" {{ $selectedSemName == 'Semester 2 (Genap)' ? 'selected' : '' }}>Semester 2 (Genap)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
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

                            <div class="col-12 d-flex justify-content-end align-items-center gap-4 pt-2">
                                <a href="{{ route('matapelajaran.jadwal') }}" class="text-dark fw-bold text-decoration-none small" style="font-size: 0.95rem;">
                                    Reset
                                </a>
                                <button type="submit" class="btn btn-dark px-4 py-2" style="background-color: #212529; border-color: #212529; border-radius: 8px; font-weight: bold; font-size: 0.95rem;">
                                    Tampilkan Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if($selectedTa && $selectedSem && $selectedKelas)
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        @if(count($matapelajaran) > 0)
                        <div class="table-responsive">
                            <table class="table align-middle text-center table-bordered table-hover">
                                <thead class="table-light">
                                    <tr class="text-dark fw-bold" style="font-size: 0.95rem;">
                                        <th style="width: 50px;">No</th>
                                        <th>Hari Pelajaran</th>
                                        <th>Jam Pelajaran</th>
                                        <th class="text-start">Nama Mapel</th>
                                        <th>Semester</th>
                                        <th>Kelas</th>
                                        <th class="text-start">Guru</th>
                                        @if(auth()->user()->roles === 'admin')
                                        <th style="width: 130px;">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($matapelajaran as $index => $mp)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td class="fw-bold">{{ $mp->hari_mengajar ?? '-' }}</td>
                                            <td class="fw-semibold">{{ $mp->jam_mengajar ?? '-' }}</td>
                                            <td class="text-start fw-bold text-primary">{{ $mp->nama_mata_pelajaran }}</td>
                                            <td>{{ $mp->semester?->nama_semester ?? '-' }}</td>
                                            <td>{{ $mp->kelas?->nama_kelas ?? '-' }}</td>
                                            <td class="text-start fw-semibold text-dark">
                                                {{ $mp->guru?->pegawai?->nama_pegawai ?? '-' }}
                                            </td>
                                            @if(auth()->user()->roles === 'admin')
                                            <td>
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <a href="{{ route('matapelajaran.edit', $mp->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('matapelajaran.destroy', $mp->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-sm btn-hapus" data-nama="{{ $mp->nama_mata_pelajaran }}" title="Hapus">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-warning text-center my-3"><i class="bi bi-exclamation-triangle-fill"></i> Tidak ada jadwal mata pelajaran untuk filter yang dipilih.</div>
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
        $(document).on('click', '.btn-hapus', function (e) {
            e.preventDefault();
            const form = $(this).closest('form');
            const nama = $(this).data('nama');

            Swal.fire({
                title: 'Hapus Jadwal Mata Pelajaran?',
                html: `Anda yakin ingin menghapus jadwal mata pelajaran:<br><strong class="text-danger">${nama}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash-fill"></i> Ya, Hapus!',
                cancelButtonText: '<i class="bi bi-x-circle"></i> Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({title: 'Menghapus...', allowOutsideClick: false, didOpen: () => {Swal.showLoading()}});
                    form.submit();
                }
            });
        });
    </script>
@endpush
