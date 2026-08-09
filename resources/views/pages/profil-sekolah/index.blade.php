@extends('layouts.dashboard.template')

@section('title', 'Profil Sekolah')

@section('content')
    <div class="pagetitle">
        <h1>Profil Sekolah</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Profil Sekolah</li>
            </ol>
        </nav>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <section class="section profil-sekolah">
        <div class="row">
            <div class="col-xl-4">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body profile-card pt-4 d-flex flex-column align-items-center text-center">
                        <div class="mb-3">
                            @if ($profil->logo_sekolah)
                                <img src="{{ asset($profil->logo_sekolah) }}" alt="Logo Sekolah" class="img-fluid rounded" style="max-height: 150px; object-fit: contain;">
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-light rounded" style="width: 150px; height: 150px;">
                                    <i class="bi bi-building text-secondary" style="font-size: 4rem;"></i>
                                </div>
                            @endif
                        </div>
                        <h4 class="fw-bold text-dark mb-1">{{ $profil->nama_sekolah }}</h4>
                        <p class="text-secondary small mb-2">NPSN: {{ $profil->nis_nss_nds ?? '-' }}</p>
                        <span class="badge bg-success-light text-success rounded-pill px-3 py-1 mb-3">
                            Status: {{ $profil->status ?? 'Aktif' }}
                        </span>
                        
                        @if (auth()->user()->roles === 'admin')
                            <div class="d-flex gap-2 w-100 px-3 justify-content-center">
                                <a href="{{ route('profil-sekolah.edit', $profil->id) }}" class="btn btn-warning text-white btn-sm px-3 rounded-pill d-flex align-items-center gap-1">
                                    <i class="bi bi-pencil-square"></i> Ubah
                                </a>
                                <form action="{{ route('profil-sekolah.destroy', $profil->id) }}" method="POST" class="d-inline" id="formHapusProfil">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm px-3 rounded-pill d-flex align-items-center gap-1 btn-hapus" data-nama="{{ $profil->nama_sekolah }}">
                                        <i class="bi bi-trash-fill"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body pt-3">
                        <!-- Bordered Tabs -->
                        <ul class="nav nav-tabs nav-tabs-bordered">
                            <li class="nav-item">
                                <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#profile-overview">Detail Profil</button>
                            </li>
                        </ul>
                        
                        <div class="tab-content pt-3">
                            <div class="tab-pane fade show active profile-overview" id="profile-overview">
                                
                                @if($profil->deskripsi)
                                    <h5 class="card-title fw-bold text-dark pt-0">Deskripsi</h5>
                                    <p class="text-secondary" style="font-size: 0.95rem; line-height: 1.6;">{{ $profil->deskripsi }}</p>
                                @endif

                                <h5 class="card-title fw-bold text-dark">Identitas Sekolah</h5>

                                <div class="row mb-3">
                                    <div class="col-lg-3 col-md-4 label text-secondary fw-semibold">Nama Sekolah</div>
                                    <div class="col-lg-9 col-md-8 text-dark fw-bold">{{ $profil->nama_sekolah }}</div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-lg-3 col-md-4 label text-secondary fw-semibold">NPSN / NIS / NSS</div>
                                    <div class="col-lg-9 col-md-8 text-dark">{{ $profil->nis_nss_nds ?? '-' }}</div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-lg-3 col-md-4 label text-secondary fw-semibold">Tanggal Berdiri</div>
                                    <div class="col-lg-9 col-md-8 text-dark">
                                        {{ $profil->tanggal_berdiri ? \Carbon\Carbon::parse($profil->tanggal_berdiri)->locale('id')->isoFormat('D MMMM Y') : '-' }}
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-lg-3 col-md-4 label text-secondary fw-semibold">Tahun Ajaran Aktif</div>
                                    <div class="col-lg-9 col-md-8 text-dark fw-bold">
                                        {{ $profil->tahunAjaran->tahun_ajaran ?? '-' }}
                                    </div>
                                </div>

                                <h5 class="card-title fw-bold text-dark">Pimpinan Sekolah</h5>

                                <div class="row mb-3">
                                    <div class="col-lg-3 col-md-4 label text-secondary fw-semibold">Kepala Sekolah</div>
                                    <div class="col-lg-9 col-md-8 text-dark fw-bold">{{ $profil->nama_kepala_sekolah ?? '-' }}</div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-lg-3 col-md-4 label text-secondary fw-semibold">NIP Kepala Sekolah</div>
                                    <div class="col-lg-9 col-md-8 text-dark">{{ $profil->nip_kepala_sekolah ?? '-' }}</div>
                                </div>

                                <h5 class="card-title fw-bold text-dark">Hubungi Kami</h5>

                                <div class="row mb-3">
                                    <div class="col-lg-3 col-md-4 label text-secondary fw-semibold">No. Telepon</div>
                                    <div class="col-lg-9 col-md-8 text-dark">{{ $profil->no_telephone ?? '-' }}</div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-lg-3 col-md-4 label text-secondary fw-semibold">Email</div>
                                    <div class="col-lg-9 col-md-8 text-primary">{{ $profil->email ?? '-' }}</div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-lg-3 col-md-4 label text-secondary fw-semibold">Alamat Lengkap</div>
                                    <div class="col-lg-9 col-md-8 text-dark">
                                        {{ $profil->alamat_sekolah ?? '-' }}<br>
                                        @if($profil->kelurahan_desa || $profil->kecamatan)
                                            Kel. {{ $profil->kelurahan_desa ?? '-' }}, Kec. {{ $profil->kecamatan ?? '-' }}<br>
                                        @endif
                                        @if($profil->kabupaten_kota || $profil->provinsi)
                                            {{ $profil->kabupaten_kota ?? '-' }}, {{ $profil->provinsi ?? '-' }}
                                        @endif
                                        @if($profil->kode_pos)
                                            - {{ $profil->kode_pos }}
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div><!-- End Bordered Tabs -->

                    </div>
                </div>
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
                title: 'Hapus Profil Sekolah?',
                html: `Anda yakin ingin menghapus profil sekolah:<br><strong class="text-danger">${nama}</strong>?<br><small class="text-muted">Tindakan ini tidak dapat dibatalkan.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash-fill"></i> Ya, Hapus!',
                cancelButtonText: '<i class="bi bi-x-circle"></i> Batal',
                reverseButtons: true,
                focusCancel: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mohon Tunggu...',
                        html: 'Sedang menghapus data...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    form.submit();
                }
            });
        });
    </script>
@endpush
