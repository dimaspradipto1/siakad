@extends('layouts.dashboard.template')

@section('title', 'Edit Link Panduan G-Drive')

@section('content')
    <div class="pagetitle">
        <h1>Edit Panduan Aplikasi</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('panduan.index') }}">Panduan</a></li>
                <li class="breadcrumb-item active">Edit</li>
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
                                style="width:42px;height:42px;background:linear-gradient(135deg,#f5a623,#e8821a);">
                                <i class="bi bi-pencil-square fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Edit Link Panduan Google Drive - {{ $panduan->role }}</h5>
                                <small class="text-muted">Perbarui tautan link Google Drive atau status tampilan</small>
                            </div>
                        </div>

                        <form action="{{ route('panduan.update', $panduan->id) }}" method="POST" id="formEdit">
                            @csrf
                            @method('PUT')

                            {{-- Role / Target Pengguna --}}
                            <div class="mb-3">
                                <label for="role" class="form-label fw-semibold">
                                    Target Role / Pengguna <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <input type="text" id="role" name="role"
                                        class="form-control @error('role') is-invalid @enderror"
                                        value="{{ old('role', $panduan->role) }}"
                                        required>
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
                                        value="{{ old('judul', $panduan->judul) }}"
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
                                        value="{{ old('link_gdrive', $panduan->link_gdrive) }}"
                                        placeholder="https://drive.google.com/file/d/.../view?usp=sharing"
                                        required
                                        autocomplete="off">
                                    @error('link_gdrive')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i> Pastikan akses link diatur ke <em>"Public / Anyone with link"</em>.
                                    </small>
                                    @if($panduan->link_gdrive)
                                        <a href="{{ $panduan->link_gdrive }}" target="_blank" class="small text-primary text-decoration-none">
                                            <i class="bi bi-box-arrow-up-right me-1"></i>Tes Buka Link Saat Ini
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="row">
                                {{-- Urutan --}}
                                <div class="col-md-6 mb-3">
                                    <label for="urutan" class="form-label fw-semibold">Urutan Tampilan</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-sort-numeric-down"></i></span>
                                        <input type="number" id="urutan" name="urutan" class="form-control" value="{{ old('urutan', $panduan->urutan) }}" min="1">
                                    </div>
                                </div>

                                {{-- Icon Bootstrap --}}
                                <div class="col-md-6 mb-3">
                                    <label for="icon" class="form-label fw-semibold">Icon Bootstrap <small class="text-muted">(Opsional)</small></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-palette"></i></span>
                                        <input type="text" id="icon" name="icon" class="form-control" value="{{ old('icon', $panduan->icon) }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Status Aktif --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Status Tampilan di Halaman Login</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active"
                                        name="is_active" value="1"
                                        {{ old('is_active', $panduan->is_active) ? 'checked' : '' }}>
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
                                <button type="submit" class="btn btn-warning text-white">
                                    <i class="bi bi-check-circle me-1"></i> Perbarui Panduan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
