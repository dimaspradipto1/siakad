@extends('layouts.dashboard.template')

@section('content')
    <div class="pagetitle">
        <h1>Manajemen Guru</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Guru</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-body pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Daftar Guru</h5>
                            @if(auth()->user()->roles === 'admin')
                            <div class="d-flex gap-2">
                                <a href="{{ route('guru.template') }}" class="btn btn-secondary btn-sm" title="Download Template Import">
                                    <i class="bi bi-download"></i> Template
                                </a>
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal" title="Import Data">
                                    <i class="bi bi-file-earmark-arrow-up"></i> Import
                                </button>
                                <a href="{{ route('guru.export') }}" class="btn btn-info btn-sm text-white" title="Export Data">
                                    <i class="bi bi-file-earmark-arrow-down"></i> Export
                                </a>
                                <a href="{{ route('guru.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-circle-fill"></i> Tambah Guru
                                </a>
                            </div>
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

    <!-- Modal Import -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('guru.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Data Guru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih File Excel (.xlsx, .xls, .csv)</label>
                            <input class="form-control" type="file" id="file" name="file" required accept=".xlsx, .xls, .csv">
                        </div>
                        <div class="alert alert-info py-2 mb-0">
                            <small><i class="bi bi-info-circle me-1"></i> Pastikan format file sesuai dengan template yang disediakan. <b>NIP Pegawai harus valid dan sudah ada di Data Pegawai.</b></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-file-earmark-arrow-up"></i> Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    @if(app()->environment('production'))
        {!! str_replace('http:', 'https:', $dataTable->scripts()) !!}
    @else
        {!! $dataTable->scripts() !!}
    @endif
    <script>
        // SweetAlert Konfirmasi Hapus menggunakan Event Delegation (karena AJAX DataTable render dinamis)
        $(document).on('click', '.btn-hapus', function (e) {
            e.preventDefault();
            const form = $(this).closest('form');
            const nama = $(this).data('nama');

            Swal.fire({
                title: 'Hapus Data Guru?',
                html: `Anda yakin ingin menghapus data guru:<br><strong class="text-danger">${nama}</strong>?<br><small class="text-muted">Tindakan ini tidak dapat dibatalkan.</small>`,
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
