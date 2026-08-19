@extends('layouts.dashboard.template')

@section('title', 'Edit Data Pegawai')

@section('content')
    <div class="pagetitle mb-4">
        <h1 class="fw-bold text-dark fs-4">Form Edit Pegawai</h1>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">

                        <form action="{{ route('pegawai.update', $pegawai->id) }}" method="POST" id="formEditPegawai">
                            @csrf
                            @method('PUT')
                            
                            {{-- Row 1: NIP, Nama Lengkap, Jenis Kelamin --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="nip" class="form-label fw-medium text-secondary">NIP</label>
                                    <input type="text" id="nip" name="nip" 
                                        class="form-control rounded-3 @error('nip') is-invalid @enderror" 
                                        value="{{ old('nip', $pegawai->nip) }}" required placeholder="Masukkan NIP">
                                    @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="nama_pegawai" class="form-label fw-medium text-secondary">Nama Lengkap</label>
                                    <input type="text" id="nama_pegawai" name="nama_pegawai" 
                                        class="form-control rounded-3 @error('nama_pegawai') is-invalid @enderror" 
                                        value="{{ old('nama_pegawai', $pegawai->nama_pegawai) }}" required placeholder="Masukkan nama lengkap">
                                    @error('nama_pegawai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="jenis_kelamin" class="form-label fw-medium text-secondary">Jenis Kelamin</label>
                                    <select id="jenis_kelamin" name="jenis_kelamin" 
                                        class="form-select rounded-3 @error('jenis_kelamin') is-invalid @enderror" required>
                                        <option value="Laki-laki" {{ old('jenis_kelamin', $pegawai->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="Perempuan" {{ old('jenis_kelamin', $pegawai->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Row 2: Tempat Lahir, Tanggal Lahir, Agama --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="tempat_lahir" class="form-label fw-medium text-secondary">Tempat Lahir</label>
                                    <input type="text" id="tempat_lahir" name="tempat_lahir" 
                                        class="form-control rounded-3 @error('tempat_lahir') is-invalid @enderror" 
                                        value="{{ old('tempat_lahir', $pegawai->tempat_lahir) }}" required placeholder="Masukkan tempat lahir">
                                    @error('tempat_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="tgl_lahir" class="form-label fw-medium text-secondary">Tanggal Lahir</label>
                                    <input type="date" id="tgl_lahir" name="tgl_lahir" 
                                        class="form-control rounded-3 @error('tgl_lahir') is-invalid @enderror" 
                                        value="{{ old('tgl_lahir', $pegawai->tgl_lahir ? $pegawai->tgl_lahir->format('Y-m-d') : '') }}" required>
                                    @error('tgl_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="agama" class="form-label fw-medium text-secondary">Agama</label>
                                    <select id="agama" name="agama" 
                                        class="form-select rounded-3 @error('agama') is-invalid @enderror" required>
                                        @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agm)
                                            <option value="{{ $agm }}" {{ old('agama', $pegawai->agama) == $agm ? 'selected' : '' }}>{{ $agm }}</option>
                                        @endforeach
                                    </select>
                                    @error('agama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Row 3: Pendidikan Terakhir, Golongan, Email --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="pendidikan_terakhir" class="form-label fw-medium text-secondary">Pendidikan Terakhir</label>
                                    <input type="text" id="pendidikan_terakhir" name="pendidikan_terakhir" 
                                        class="form-control rounded-3 @error('pendidikan_terakhir') is-invalid @enderror" 
                                        value="{{ old('pendidikan_terakhir', $pegawai->pendidikan_terakhir ?? ($pegawai->guru->pendidikan_terakhir ?? '')) }}" placeholder="Masukkan Pendidikan Terakhir">
                                    @error('pendidikan_terakhir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="golongan" class="form-label fw-medium text-secondary">Golongan</label>
                                    <select id="golongan" name="golongan" class="form-select rounded-3 @error('golongan') is-invalid @enderror">
                                        <option value="">-- Pilih Golongan --</option>
                                        @foreach(['I/a', 'I/b', 'I/c', 'I/d', 'II/a', 'II/b', 'II/c', 'II/d', 'III/a', 'III/b', 'III/c', 'III/d', 'IV/a', 'IV/b', 'IV/c', 'IV/d', 'IX (PPPK)', 'Non-ASN'] as $gol)
                                            <option value="{{ $gol }}" {{ old('golongan', $pegawai->golongan ?? ($pegawai->guru->golongan ?? '')) == $gol ? 'selected' : '' }}>{{ $gol }}</option>
                                        @endforeach
                                    </select>
                                    @error('golongan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="email" class="form-label fw-medium text-secondary">Email</label>
                                    <input type="email" id="email" name="email" 
                                        class="form-control rounded-3 @error('email') is-invalid @enderror" 
                                        value="{{ old('email', $pegawai->user->email ?? '') }}" placeholder="email@domain.com">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Row 4: Alamat Lengkap, No. Whatsapp, Role --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="alamat" class="form-label fw-medium text-secondary">Alamat Lengkap</label>
                                    <textarea id="alamat" name="alamat" rows="2" 
                                        class="form-control rounded-3 @error('alamat') is-invalid @enderror" 
                                        placeholder="Masukkan alamat lengkap">{{ old('alamat', $pegawai->alamat) }}</textarea>
                                    @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="nomor_wa" class="form-label fw-medium text-secondary">No. Whatsapp</label>
                                    <input type="text" id="nomor_wa" name="nomor_wa" 
                                        class="form-control rounded-3 @error('nomor_wa') is-invalid @enderror" 
                                        value="{{ old('nomor_wa', $pegawai->nomor_wa) }}" placeholder="081234567890">
                                    @error('nomor_wa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="role" class="form-label fw-medium text-secondary">Role</label>
                                    <select id="role" name="role" class="form-select rounded-3 @error('role') is-invalid @enderror">
                                        @php $currentRole = $pegawai->user->roles ?? strtolower($pegawai->jabatan); @endphp
                                        <option value="pegawai" {{ old('role', $currentRole) == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                                        <option value="guru" {{ old('role', $currentRole) == 'guru' ? 'selected' : '' }}>Guru</option>
                                        <option value="wali kelas" {{ old('role', $currentRole) == 'wali kelas' ? 'selected' : '' }}>Wali Kelas</option>
                                        <option value="kepala sekolah" {{ old('role', $currentRole) == 'kepala sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                                        <option value="admin" {{ old('role', $currentRole) == 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Row 5: Password Baru (Opsional), Konfirmasi Password, Status --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label for="password" class="form-label fw-medium text-secondary">Password Baru (Opsional)</label>
                                    <div class="input-group">
                                        <input type="password" id="password" name="password" 
                                            class="form-control rounded-start-3 @error('password') is-invalid @enderror" 
                                            placeholder="Kosongkan jika tidak diubah"
                                            autocomplete="new-password">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="password_confirmation" class="form-label fw-medium text-secondary">Konfirmasi Password</label>
                                    <div class="input-group">
                                        <input type="password" id="password_confirmation" name="password_confirmation" 
                                            class="form-control rounded-start-3 @error('password_confirmation') is-invalid @enderror" 
                                            placeholder="Konfirmasi Password Baru"
                                            autocomplete="new-password">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    @error('password_confirmation')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="status" class="form-label fw-medium text-secondary">Status</label>
                                    <select id="status" name="status" class="form-select rounded-3 @error('status') is-invalid @enderror">
                                        <option value="Aktif" {{ old('status', $pegawai->status ?? 'Aktif') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Non-Aktif" {{ old('status', $pegawai->status ?? '') == 'Non-Aktif' ? 'selected' : '' }}>Non-Aktif</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Row 6: Jabatan --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-12">
                                    <label for="jabatan" class="form-label fw-medium text-secondary">Jabatan (Master Jabatan)</label>
                                    <select id="jabatan" name="jabatan" class="form-select rounded-3 @error('jabatan') is-invalid @enderror">
                                        <option value="" disabled {{ old('jabatan', $pegawai->jabatan) ? '' : 'selected' }}>-- Pilih Jabatan --</option>
                                        @foreach($jabatans as $j)
                                            <option value="{{ $j->nama_jabatan }}" {{ old('jabatan', $pegawai->jabatan) == $j->nama_jabatan ? 'selected' : '' }}>
                                                {{ $j->nama_jabatan }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <!-- ==========================================
                                 FOOTER BUTTONS
                                 ========================================== -->
                            <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top">
                                <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3">
                                    Batal
                                </a>
                                <button type="submit" class="btn btn-dark px-4 py-2 rounded-3">
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

@push('script')
    <script>
        $(document).on('click', '.toggle-password', function () {
            const targetId = $(this).data('target');
            const input = $('#' + targetId);
            const icon = $(this).find('i');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
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
