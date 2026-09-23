@extends('layouts.dashboard.template')

@section('title', 'Tambah Link Panduan G-Drive')

@section('content')
    <div class="pagetitle">
        <h1>Tambah Panduan Aplikasi</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('panduan.index') }}">Panduan</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body pt-4">

                        <div class="d-flex align-items-center gap-2 mb-4">
                            <div class="d-flex align-items-center justify-content-center rounded-circle text-white shadow-sm"
                                style="width:42px;height:42px;background:linear-gradient(135deg,#1a4fad,#0d9fd8);">
                                <i class="bi bi-google fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Tambah Link Panduan Google Drive</h5>
                                <small class="text-muted">Masukkan link URL Google Drive yang akan disinkronkan ke halaman login</small>
                            </div>
                        </div>

                        <form action="{{ route('panduan.store') }}" method="POST" id="formCreate">
                            @csrf

                            {{-- Role / Target Pengguna --}}
                            <div class="mb-3">
                                <label for="role" class="form-label fw-semibold">
                                    Target Role / Pengguna <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                        <option value="">-- Pilih Target Role --</option>
                                        @foreach($defaultRoles as $rName => $icon)
                                            <option value="{{ $rName }}" {{ old('role') === $rName ? 'selected' : '' }}>
                                                {{ $rName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Judul Panduan --}}
                            <div class="mb-3">
                                <label for="judul" class="form-label fw-semibold">
                                    Judul Panduan <small class="text-muted">(Opsional)</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                    <input type="text" id="judul" name="judul"
                                        class="form-control @error('judul') is-invalid @enderror"
                                        value="{{ old('judul') }}"
                                        placeholder="Contoh: Buku Petunjuk Penggunaan Admin"
                                        autocomplete="off">
                                    @error('judul')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Link Google Drive (UTAMA) --}}
                            <div class="mb-3">
                                <label for="link_gdrive" class="form-label fw-semibold">
                                    Link Google Drive <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-primary"><i class="bi bi-link-45deg fs-5"></i></span>
                                    <input type="url" id="link_gdrive" name="link_gdrive"
                                        class="form-control @error('link_gdrive') is-invalid @enderror"
                                        value="{{ old('link_gdrive') }}"
                                        placeholder="https://drive.google.com/file/d/.../view?usp=sharing"
                                        required
                                        autocomplete="off">
                                    @error('link_gdrive')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted mt-1 d-block">
                                    <i class="bi bi-info-circle me-1"></i> Pastikan izin link Google Drive disetel ke <em>"Siapa saja yang memiliki link dapat melihat" (Anyone with the link can view)</em>.
                                </small>
                            </div>

                            <div class="row">
                                {{-- Urutan --}}
                                <div class="col-md-6 mb-3">
                                    <label for="urutan" class="form-label fw-semibold">Urutan Tampilan</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-sort-numeric-down"></i></span>
                                        <input type="number" id="urutan" name="urutan" class="form-control" value="{{ old('urutan', 1) }}" min="1">
                                    </div>
                                </div>

                                {{-- Icon Bootstrap --}}
                                <div class="col-md-6 mb-3">
                                    <label for="icon" class="form-label fw-semibold">Icon Bootstrap <small class="text-muted">(Opsional)</small></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-palette"></i></span>
                                        <input type="text" id="icon" name="icon" class="form-control" value="{{ old('icon') }}" placeholder="Contoh: bi bi-shield-check">
                                    </div>
                                </div>
                            </div>

                            {{-- Status Aktif --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Status Tampilan di Halaman Login</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active"
                                        name="is_active" value="1"
                                        {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Aktifkan <strong>(Muncul di halaman login)</strong>
                                    </label>
                                </div>
                            </div>

                            <hr>

                            {{-- Tombol --}}
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('panduan.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Simpan Panduan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
