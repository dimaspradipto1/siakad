@extends('layouts.dashboard.template')

@section('title', 'Edit Catatan Siswa')

@section('content')
    <div class="pagetitle">
        <h1>Edit Catatan Siswa</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('catatansiswa.index') }}">Catatan Siswa</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-body pt-4">

                        <form action="{{ route('catatansiswa.update', $catatansiswa->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="siswa_id" class="form-label fw-semibold">Siswa <span class="text-danger">*</span></label>
                                    <select id="siswa_id" name="siswa_id" class="form-select select2 @error('siswa_id') is-invalid @enderror">
                                        @foreach($siswas as $siswa)
                                            <option value="{{ $siswa->id }}" {{ old('siswa_id', $catatansiswa->siswa_id) == $siswa->id ? 'selected' : '' }}>{{ $siswa->nama_siswa }} ({{ $siswa->kelas->nama_kelas ?? '-' }})</option>
                                        @endforeach
                                    </select>
                                    @error('siswa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <label for="jenis_catatan_id" class="form-label fw-semibold">Jenis Catatan <span class="text-danger">*</span></label>
                                    <select id="jenis_catatan_id" name="jenis_catatan_id" class="form-select @error('jenis_catatan_id') is-invalid @enderror">
                                        @foreach($jenisCatatans as $jc)
                                            <option value="{{ $jc->id }}" {{ old('jenis_catatan_id', $catatansiswa->jenis_catatan_id) == $jc->id ? 'selected' : '' }}>{{ $jc->nama_jenis_catatan }}</option>
                                        @endforeach
                                    </select>
                                    @error('jenis_catatan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="guru_id" class="form-label fw-semibold">Guru Pelapor <span class="text-danger">*</span></label>
                                    <select id="guru_id" name="guru_id" class="form-select select2 @error('guru_id') is-invalid @enderror">
                                        @foreach($gurus as $guru)
                                            <option value="{{ $guru->id }}" {{ old('guru_id', $catatansiswa->guru_id) == $guru->id ? 'selected' : '' }}>{{ $guru->pegawai->nama_pegawai ?? '-' }}</option>
                                        @endforeach
                                    </select>
                                    @error('guru_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mt-3 mt-md-0">
                                    <label for="semester_id" class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                                     <select id="semester_id" name="semester_id" class="form-select @error('semester_id') is-invalid @enderror">
                                         @foreach($semesters as $sem)
                                             <option value="{{ $sem->id }}" data-tahun-ajaran="{{ $sem->tahun_ajaran_id }}" {{ old('semester_id', $catatansiswa->semester_id) == $sem->id ? 'selected' : '' }}>{{ $sem->nama_semester }}</option>
                                         @endforeach
                                     </select>
                                    @error('semester_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mt-3 mt-md-0">
                                    <label for="tahun_ajaran_id" class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                                    <select id="tahun_ajaran_id" name="tahun_ajaran_id" class="form-select @error('tahun_ajaran_id') is-invalid @enderror">
                                        @foreach($tahunAjarans as $ta)
                                            <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id', $catatansiswa->tahun_ajaran_id) == $ta->id ? 'selected' : '' }}>{{ $ta->nama_tahun_ajaran }}</option>
                                        @endforeach
                                    </select>
                                    @error('tahun_ajaran_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="tanggal" class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" id="tanggal" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', $catatansiswa->tanggal) }}">
                                    @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <label for="status" class="form-label fw-semibold">Status Tindak Lanjut <span class="text-danger">*</span></label>
                                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="Belum Diproses" {{ old('status', $catatansiswa->status) == 'Belum Diproses' ? 'selected' : '' }}>Belum Diproses</option>
                                        <option value="Sedang Diproses" {{ old('status', $catatansiswa->status) == 'Sedang Diproses' ? 'selected' : '' }}>Sedang Diproses</option>
                                        <option value="Selesai" {{ old('status', $catatansiswa->status) == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="isi_catatan" class="form-label fw-semibold">Isi Catatan <span class="text-danger">*</span></label>
                                <textarea id="isi_catatan" name="isi_catatan" rows="3" class="form-control @error('isi_catatan') is-invalid @enderror">{{ old('isi_catatan', $catatansiswa->isi_catatan) }}</textarea>
                                @error('isi_catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <label for="tindak_lanjut" class="form-label fw-semibold">Tindak Lanjut</label>
                                <textarea id="tindak_lanjut" name="tindak_lanjut" rows="2" class="form-control @error('tindak_lanjut') is-invalid @enderror">{{ old('tindak_lanjut', $catatansiswa->tindak_lanjut) }}</textarea>
                                @error('tindak_lanjut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                <a href="{{ route('catatansiswa.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
