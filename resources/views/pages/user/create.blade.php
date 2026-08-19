@extends('layouts.dashboard.template')

@section('title', 'Tambah Pengguna')

@section('content')
    <div class="pagetitle">
        <h1>Tambah Pengguna</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('user.index') }}">Pengguna</a></li>
                <li class="breadcrumb-item active">Tambah</li>
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
                                <i class="bi bi-person-plus-fill fs-5"></i>
                            </div>
                            <h5 class="mb-0">Form Tambah Pengguna Baru</h5>
                        </div>

                        <form action="{{ route('user.store') }}" method="POST" id="formCreate">
                            @csrf

                            {{-- Nama --}}
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" id="name" name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}"
                                        placeholder="Masukkan nama lengkap"
                                        autocomplete="off">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">
                                    Alamat Email <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" id="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}"
                                        placeholder="nama@email.com"
                                        autocomplete="off">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Role (Bisa Lebih Dari Satu) --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold d-block">
                                    Role Akses <span class="text-danger">*</span>
                                    <small class="text-muted fw-normal d-block">Pilih satu atau beberapa role untuk pengguna ini:</small>
                                </label>
                                <div class="row g-2 p-3 bg-light rounded-3 border @error('roles') border-danger @enderror">
                                    @foreach ($roles as $role)
                                        @php
                                            $oldRoles = old('roles', []);
                                            $isChecked = is_array($oldRoles) ? in_array($role, $oldRoles) : ($oldRoles === $role);
                                        @endphp
                                        <div class="col-sm-6 col-md-4">
                                            <div class="form-check p-2 bg-white rounded border d-flex align-items-center gap-2">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="roles[]" 
                                                       id="role_{{ Str::slug($role) }}" value="{{ $role }}"
                                                       {{ $isChecked ? 'checked' : '' }}>
                                                <label class="form-check-label fw-medium text-dark cursor-pointer text-capitalize" for="role_{{ Str::slug($role) }}">
                                                    {{ $role }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('roles')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                {{-- Password --}}
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label fw-semibold">
                                        Password <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" id="password" name="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Min. 6 karakter">
                                        <button type="button" class="btn btn-outline-secondary toggle-pwd"
                                            data-target="password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Konfirmasi Password --}}
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label fw-semibold">
                                        Konfirmasi Password <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" id="password_confirmation" name="password_confirmation"
                                            class="form-control"
                                            placeholder="Ulangi password">
                                        <button type="button" class="btn btn-outline-secondary toggle-pwd"
                                            data-target="password_confirmation">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Status Aktif --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Status Akun</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active"
                                        name="is_active" value="1"
                                        {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Akun <strong>Aktif</strong>
                                    </label>
                                </div>
                            </div>

                            <hr>

                            {{-- Tombol --}}
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('user.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Simpan
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
        // Toggle show/hide password
        document.querySelectorAll('.toggle-pwd').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const inputId = this.dataset.target;
                const input = document.getElementById(inputId);
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'bi bi-eye-slash';
                } else {
                    input.type = 'password';
                    icon.className = 'bi bi-eye';
                }
            });
        });

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
