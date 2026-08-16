@extends('layouts.dashboard.template')

@section('title', 'Edit Materi Pembelajaran')

@section('content')
    <div class="pagetitle">
        <h1 class="text-primary fw-bold">Edit Materi Pembelajaran</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('materipembelajaran.index') }}">Materi Pembelajaran</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        <h5 class="card-title text-dark fw-bold mb-4 p-0">Form Edit Data Materi Pembelajaran</h5>

                        <form action="{{ route('materipembelajaran.update', $materipembelajaran->id) }}" method="POST" enctype="multipart/form-data" class="row g-4">
                            @csrf
                            @method('PUT')
                            
                            <div class="col-md-6">
                                <label for="tahun_ajaran_id" class="form-label fw-semibold text-dark">Tahun Ajaran <span class="text-danger">*</span></label>
                                <select id="tahun_ajaran_id" name="tahun_ajaran_id" class="form-select py-2 @error('tahun_ajaran_id') is-invalid @enderror" style="border-radius: 8px;">
                                    <option value="" disabled></option>
                                    @foreach($tahunAjarans as $ta)
                                        <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id', $materipembelajaran->tahun_ajaran_id) == $ta->id ? 'selected' : '' }}>
                                            {{ $ta->nama_tahun_ajaran }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tahun_ajaran_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="semester_name" class="form-label fw-semibold text-dark">Semester <span class="text-danger">*</span></label>
                                <select id="semester_name" name="semester_name" class="form-select py-2 @error('semester_name') is-invalid @enderror" style="border-radius: 8px;">
                                    <option value="" disabled>-- Pilih Semester --</option>
                                    @if(isset($semesters))
                                        @foreach($semesters as $sem)
                                            <option value="{{ $sem->nama_semester }}" {{ old('semester_name', $materipembelajaran->semester ? $materipembelajaran->semester->nama_semester : '') == $sem->nama_semester ? 'selected' : '' }}>
                                                {{ $sem->nama_semester }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('semester_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="kelas_id" class="form-label fw-semibold text-dark">Kelas <span class="text-danger">*</span></label>
                                <select id="kelas_id" name="kelas_id" class="form-select py-2 @error('kelas_id') is-invalid @enderror" style="border-radius: 8px;">
                                    <option value="" disabled></option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" {{ old('kelas_id', $materipembelajaran->kelas_id) == $k->id ? 'selected' : '' }}>
                                            {{ $k->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="nama_mata_pelajaran" class="form-label fw-semibold text-dark">Mata Pelajaran <span class="text-danger">*</span></label>
                                <select id="nama_mata_pelajaran" name="nama_mata_pelajaran" class="form-select py-2 @error('nama_mata_pelajaran') is-invalid @enderror" style="border-radius: 8px;">
                                    <option value="" disabled></option>
                                    @foreach($uniqueMapels as $name)
                                        <option value="{{ $name }}" {{ old('nama_mata_pelajaran', $materipembelajaran->mataPelajaran ? $materipembelajaran->mataPelajaran->nama_mata_pelajaran : '') == $name ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('nama_mata_pelajaran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="judul_materi" class="form-label fw-semibold text-dark">Judul Materi <span class="text-danger">*</span></label>
                                <input type="text" id="judul_materi" name="judul_materi" class="form-control py-2 @error('judul_materi') is-invalid @enderror" value="{{ old('judul_materi', $materipembelajaran->judul_materi) }}" placeholder="Masukkan judul materi" style="border-radius: 8px;">
                                @error('judul_materi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="deskripsi_materi" class="form-label fw-semibold text-dark">Deskripsi Materi</label>
                                <textarea id="deskripsi_materi" name="deskripsi_materi" rows="6" class="form-control @error('deskripsi_materi') is-invalid @enderror" placeholder="Masukkan deskripsi materi pelajaran" style="border-radius: 8px;">{{ old('deskripsi_materi', $materipembelajaran->deskripsi_materi) }}</textarea>
                                @error('deskripsi_materi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="file_materi" class="form-label fw-semibold text-dark">File Materi</label>
                                <input type="file" id="file_materi" name="file_materi" class="form-control py-2 @error('file_materi') is-invalid @enderror" style="border-radius: 8px;">
                                <div class="form-text text-muted">Mendukung format: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP, RAR, atau Gambar (Max. 10 MB). Biarkan kosong jika tidak ingin mengubah file.</div>
                                @if($materipembelajaran->file_materi)
                                    <div class="mt-2 text-muted" style="font-size: 0.85rem;">
                                        <i class="bi bi-file-earmark-check-fill text-success"></i> File saat ini: 
                                        <a href="{{ route('materipembelajaran.download', $materipembelajaran->id) }}" class="fw-semibold" target="_blank">
                                            {{ basename($materipembelajaran->file_materi) }}
                                        </a> ({{ $materipembelajaran->file_size }})
                                    </div>
                                @endif
                                @error('file_materi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 d-flex justify-content-end align-items-center gap-4 pt-2">
                                <a href="{{ route('materipembelajaran.index') }}" class="text-dark fw-bold text-decoration-none small" style="font-size: 0.95rem;">
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
