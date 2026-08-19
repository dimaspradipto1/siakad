@extends('layouts.dashboard.template')

@section('title', 'Edit Pengguna')

@section('content')
    <div class="pagetitle">
        <h1>Edit Pengguna</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('user.index') }}">Pengguna</a></li>
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
                            <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
                                style="width:42px;height:42px;font-size:18px;background:linear-gradient(135deg,#f5a623,#e8821a);">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h5 class="mb-0">Edit Pengguna</h5>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                        </div>

                        <form action="{{ route('user.update', $user->id) }}" method="POST" id="formEdit">
                            @csrf
                            @method('PUT')

                            {{-- Nama --}}
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" id="name" name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $user->name) }}"
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
                                        value="{{ old('email', $user->email) }}"
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
                                    @php
                                        $userRoles = $user->getRolesList();
                                    @endphp
                                    @foreach ($roles as $role)
                                        @php
                                            $oldRoles = old('roles', $userRoles);
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

                            {{-- Password (opsional) --}}
                            <div class="mb-2">
                                <label class="form-label fw-semibold">
                                    Password Baru
                                    <small class="text-muted fw-normal">(kosongkan jika tidak ingin mengubah)</small>
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" id="password" name="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Password baru">
                                        <button type="button" class="btn btn-outline-secondary toggle-pwd"
                                            data-target="password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" id="password_confirmation"
                                            name="password_confirmation"
                                            class="form-control"
                                            placeholder="Konfirmasi password baru">
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
                                        {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Akun <strong>Aktif</strong>
                                    </label>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                {{-- Info terakhir diperbarui --}}
                                <small class="text-muted">
                                    <i class="bi bi-clock-history me-1"></i>
                                    Dibuat: {{ $user->created_at->locale('id')->isoFormat('D MMM Y') }}
                                </small>

                                <div class="d-flex gap-2">
                                    <a href="{{ route('user.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Kembali
                                    </a>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-save me-1"></i> Update
                                    </button>
                                </div>
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
                title: 'Gagal Memperbarui!',
                html: `<ul class="text-start ps-3 mb-0">{!! implode('', $errors->all('<li>:message</li>')) !!}</ul>`,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Oke, Perbaiki',
            });
        @endif
    </script>
@endpush
