@extends('layouts.dashboard.template')

@section('title', 'Edit Pengumuman')

@section('content')
    <div class="pagetitle">
        <h1 class="text-primary fw-bold">Edit Pengumuman</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pengumuman.index') }}">Pengumuman</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        <h5 class="card-title text-dark fw-bold mb-4 p-0">Form Edit Data Pengumuman</h5>

                        <form action="{{ route('pengumuman.update', $pengumuman->id) }}" method="POST" class="row g-4">
                            @csrf
                            @method('PUT')
                            
                            <div class="col-md-6">
                                <label for="tahun_ajaran_id" class="form-label fw-semibold text-dark">Tahun Ajaran <span class="text-danger">*</span></label>
                                <select id="tahun_ajaran_id" name="tahun_ajaran_id" class="form-select py-2 @error('tahun_ajaran_id') is-invalid @enderror" style="border-radius: 8px;">
                                    <option value="" disabled></option>
                                    @foreach($tahunAjarans as $ta)
                                        <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id', $pengumuman->tahun_ajaran_id) == $ta->id ? 'selected' : '' }}>
                                            {{ $ta->nama_tahun_ajaran }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tahun_ajaran_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="semester_id" class="form-label fw-semibold text-dark">Semester <span class="text-danger">*</span></label>
                                <select id="semester_id" name="semester_id" class="form-select py-2 @error('semester_id') is-invalid @enderror" style="border-radius: 8px;">
                                    <option value="" disabled>-- Pilih Semester --</option>
                                    @foreach($semesters as $sem)
                                        <option value="{{ $sem->id }}" data-tahun-ajaran="{{ $sem->tahun_ajaran_id }}" {{ old('semester_id', $pengumuman->semester_id) == $sem->id ? 'selected' : '' }}>
                                            {{ $sem->nama_semester }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('semester_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="kelas_id" class="form-label fw-semibold text-dark">Kelas</label>
                                <select id="kelas_id" name="kelas_id" class="form-select py-2 @error('kelas_id') is-invalid @enderror" style="border-radius: 8px;">
                                    <option value="">Semua Kelas</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" {{ old('kelas_id', $pengumuman->kelas_id) == $k->id ? 'selected' : '' }}>
                                            {{ $k->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="mata_pelajaran_id" class="form-label fw-semibold text-dark">Mata Pelajaran</label>
                                <select id="mata_pelajaran_id" name="mata_pelajaran_id" class="form-select py-2 @error('mata_pelajaran_id') is-invalid @enderror" style="border-radius: 8px;">
                                    <option value="">Semua Mata Pelajaran</option>
                                    @foreach($matapelajarans as $mp)
                                        <option value="{{ $mp->id }}" {{ old('mata_pelajaran_id', $pengumuman->mata_pelajaran_id) == $mp->id ? 'selected' : '' }}>
                                            {{ $mp->nama_mata_pelajaran }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('mata_pelajaran_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="judul" class="form-label fw-semibold text-dark">Judul Pengumuman <span class="text-danger">*</span></label>
                                <input type="text" id="judul" name="judul" class="form-control py-2 @error('judul') is-invalid @enderror" value="{{ old('judul', $pengumuman->judul) }}" placeholder="Masukkan judul pengumuman" style="border-radius: 8px;">
                                @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="keterangan" class="form-label fw-semibold text-dark">Isi Pengumuman <span class="text-danger">*</span></label>
                                <textarea id="keterangan" name="keterangan" rows="8" class="form-control @error('keterangan') is-invalid @enderror" placeholder="Tulis isi pengumuman secara detail..." style="border-radius: 8px;">{{ old('keterangan', $pengumuman->keterangan) }}</textarea>
                                @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 d-flex justify-content-end align-items-center gap-4 pt-2">
                                <a href="{{ route('pengumuman.index') }}" class="text-dark fw-bold text-decoration-none small" style="font-size: 0.95rem;">
                                    Batal
                                </a>
                                <button type="submit" class="btn btn-dark px-4 py-2" style="background-color: #212529; border-color: #212529; border-radius: 8px; font-weight: bold; font-size: 0.95rem;">
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
