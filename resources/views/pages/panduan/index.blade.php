@extends('layouts.dashboard.template')

@section('title', 'Panduan Aplikasi')

@section('content')
    <div class="pagetitle">
        <h1>Panduan Aplikasi & Link G-Drive</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Panduan Aplikasi</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        {{-- Info Alert --}}
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-info-circle-fill fs-5 text-primary"></i>
            <div>
                <strong>Sinkronisasi Login:</strong> Link Google Drive panduan yang aktif di bawah ini akan otomatis tampil dan dapat diakses pengguna di halaman <strong>Login</strong> pada menu badge di bawah formulir login.
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-body pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="card-title mb-0">Daftar Link Panduan Per Role</h5>
                                <small class="text-muted">Kelola link Google Drive dokumen panduan untuk setiap peran pengguna</small>
                            </div>
                            @if(auth()->user()->roles === 'admin')
                            <a href="{{ route('panduan.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle-fill"></i> Tambah Panduan
                            </a>
                            @endif
                        </div>
                        <div class="table-responsive">
                            {{ $dataTable->table(['class' => 'table table-bordered table-hover align-middle', 'style' => 'width:100%']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('script')
    @if(app()->environment('production'))
        {!! str_replace('http:', 'https:', $dataTable->scripts()) !!}
    @else
        {!! $dataTable->scripts() !!}
    @endif
    <script>
        // SweetAlert Konfirmasi Hapus
        $(document).on('click', '.btn-hapus', function (e) {
            e.preventDefault();
            const form = $(this).closest('form');
            const nama = $(this).data('nama');

            Swal.fire({
                title: 'Hapus Panduan?',
                html: `Anda yakin ingin menghapus link panduan untuk role:<br><strong class="text-danger">${nama}</strong>?<br><small class="text-muted">Item ini tidak akan muncul lagi di halaman login.</small>`,
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
