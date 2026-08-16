@extends('layouts.dashboard.template')

@section('title', 'Edit Semester')

@section('content')
    <div class="pagetitle">
        <h1>Edit Semester</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('semester.index') }}">Semester</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body pt-4">

                        <div class="d-flex align-items-center gap-2 mb-4">
                            <div class="d-flex align-items-center justify-content-center rounded-circle text-white"
                                style="width:42px;height:42px;background:linear-gradient(135deg,#1a4fad,#0d9fd8);">
                                <i class="bi bi-pencil-square fs-5"></i>
                            </div>
                            <h5 class="mb-0">Form Edit Semester</h5>
                        </div>

                        <form action="{{ route('semester.update', $semester->id) }}" method="POST" id="formEdit">
                            @csrf
                            @method('PUT')

                            {{-- Pilih Tahun Ajaran --}}
                            <div class="mb-3">
                                <label for="tahun_ajaran_id" class="form-label fw-semibold">
                                    Tahun Ajaran <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                    <select id="tahun_ajaran_id" name="tahun_ajaran_id"
                                        class="form-select @error('tahun_ajaran_id') is-invalid @enderror">
                                        <option value="" disabled>-- Pilih Tahun Ajaran --</option>
                                        @foreach($tahunAjarans as $ta)
                                            <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id', $semester->tahun_ajaran_id) == $ta->id ? 'selected' : '' }}>
                                                {{ $ta->tahun_mulai }}/{{ $ta->tahun_selesai }}
                                                @if($ta->status === 'Aktif') — ✅ Aktif @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tahun_ajaran_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Nama Semester --}}
                            <div class="mb-4">
                                <label for="nama_semester" class="form-label fw-semibold">
                                    Nama Semester <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                    <input type="text" id="nama_semester" name="nama_semester" list="semester_list"
                                        class="form-select @error('nama_semester') is-invalid @enderror"
                                        value="{{ old('nama_semester', $semester->nama_semester) }}"
                                        placeholder="Ketik atau pilih semester" required>
                                    <datalist id="semester_list">
                                        <option value="Semester 1 (Ganjil)">
                                        <option value="Semester 2 (Genap)">
                                        <option value="Semester Antara">
                                        <option value="Semester Pendek">
                                        <option value="Semester 3">
                                    </datalist>
                                </div>
                                @error('nama_semester')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted">
                                    <i class="bi bi-info-circle me-1"></i>Pilih dari daftar saran atau ketik nama semester yang disesuaikan.
                                </div>
                            </div>

                            {{-- Tombol Aksi --}}
                            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                <a href="{{ route('semester.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Simpan Perubahan
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
        // SweetAlert error validasi jika ada error di server-side
        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan!',
                html: `<ul class="text-start ps-3 mb-0">{!! implode('', $errors->all('<li>:message</li>')) !!}</ul>`,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Oke, Perbaiki',
            });
        @endif
    </script>
@endpush
