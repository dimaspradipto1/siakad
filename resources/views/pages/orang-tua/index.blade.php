@extends('layouts.dashboard.template')

@section('title', 'Manajemen Orang Tua')

@section('content')
    <div class="pagetitle">
        <h1>Manajemen Orang Tua</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Orang Tua</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-body pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Daftar Orang Tua / Wali</h5>
                            @if(auth()->user()->roles === 'admin')
                            <div class="d-flex gap-2">
                                @if(auth()->user()->roles !== 'admin')
                                <a href="{{ route('orang-tua.template') }}" class="btn btn-secondary btn-sm" title="Download Template Import">
                                    <i class="bi bi-download"></i> Template
                                </a>
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal" title="Import Data">
                                    <i class="bi bi-file-earmark-arrow-up"></i> Import
                                </button>
                                <a href="{{ route('orang-tua.export') }}" class="btn btn-info btn-sm text-white" title="Export Data">
                                    <i class="bi bi-file-earmark-arrow-down"></i> Export
                                </a>
                                @endif
                                <a href="{{ route('orang-tua.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-circle-fill"></i> Tambah Data
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
                <form action="{{ route('orang-tua.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Data Orang Tua</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih File Excel (.xlsx, .xls, .csv)</label>
                            <input class="form-control" type="file" id="file" name="file" required accept=".xlsx, .xls, .csv">
                        </div>
                        <div class="alert alert-info py-2 mb-0">
                            <small><i class="bi bi-info-circle me-1"></i> Pastikan format file sesuai dengan template yang disediakan.</small>
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
        $(document).on('click', '.btn-hapus', function (e) {
            e.preventDefault();
            const form = $(this).closest('form');
            const nama = $(this).data('nama');

            Swal.fire({
                title: 'Hapus Data?',
                html: `Anda yakin ingin menghapus data:<br><strong class="text-danger">${nama}</strong>?<br><small class="text-muted">Data siswa yang terkait dengan orang tua ini juga mungkin akan terpengaruh.</small>`,
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
