@extends('layouts.dashboard.template')

@section('title', 'Mata Pelajaran Aktif')

@section('content')
    <div class="pagetitle">
        <h1>Manajemen Mata Pelajaran Aktif</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Mata Pelajaran Aktif</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <!-- Filter Card -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body pt-3">
                        <h5 class="card-title mb-3 fs-6"><i class="bi bi-funnel me-1"></i> Filter Data Mata Pelajaran Aktif</h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="filter_tahun_ajaran_id" class="form-label fw-semibold text-secondary small">Tahun Ajaran</label>
                                <select id="filter_tahun_ajaran_id" class="form-select form-select-sm rounded-3">
                                    <option value="">Semua Tahun Ajaran</option>
                                    @foreach($tahunAjarans as $ta)
                                        <option value="{{ $ta->id }}">{{ $ta->nama_tahun_ajaran }} @if($ta->status == 'Aktif') (Aktif) @endif</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_semester_name" class="form-label fw-semibold text-secondary small">Semester</label>
                                <select id="filter_semester_name" class="form-select form-select-sm rounded-3">
                                    <option value="">Semua Semester</option>
                                    @if(isset($semesters))
                                        @foreach($semesters as $sem)
                                            <option value="{{ $sem->nama_semester }}">{{ $sem->nama_semester }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_guru_id" class="form-label fw-semibold text-secondary small">Nama Guru</label>
                                <select id="filter_guru_id" class="form-select form-select-sm rounded-3">
                                    <option value="">Semua Guru</option>
                                    @foreach($gurus as $g)
                                        <option value="{{ $g->id }}">{{ $g->pegawai?->nama_pegawai ?? '-' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_kelas_id" class="form-label fw-semibold text-secondary small">Kelas</label>
                                <select id="filter_kelas_id" class="form-select form-select-sm rounded-3">
                                    <option value="">Semua Kelas</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                            <button type="button" id="btn-reset-filter" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Daftar Mata Pelajaran Aktif</h5>
                            @if(auth()->user()->roles === 'admin')
                            <div class="d-flex gap-2">
                                @if(auth()->user()->roles !== 'admin')
                                <a href="{{ route('matapelajaranaktif.template') }}" class="btn btn-secondary btn-sm" title="Download Template Import">
                                    <i class="bi bi-download"></i> Template
                                </a>
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal" title="Import Data">
                                    <i class="bi bi-file-earmark-arrow-up"></i> Import
                                </button>
                                <a href="{{ route('matapelajaranaktif.export') }}" class="btn btn-info btn-sm text-white" title="Export Data">
                                    <i class="bi bi-file-earmark-arrow-down"></i> Export
                                </a>
                                @endif
                                <a href="{{ route('matapelajaranaktif.create') }}" class="btn btn-primary btn-sm">
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
                <form action="{{ route('matapelajaranaktif.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Data Mata Pelajaran Aktif</h5>
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
        $(document).ready(function() {
            $('#filter_tahun_ajaran_id, #filter_semester_name, #filter_guru_id, #filter_kelas_id').on('change', function() {
                if (window.LaravelDataTables && window.LaravelDataTables["matapelajaranaktif-table"]) {
                    window.LaravelDataTables["matapelajaranaktif-table"].draw();
                }
            });

            $('#btn-reset-filter').on('click', function() {
                $('#filter_tahun_ajaran_id').val('');
                $('#filter_semester_name').val('');
                $('#filter_guru_id').val('');
                $('#filter_kelas_id').val('');
                if (window.LaravelDataTables && window.LaravelDataTables["matapelajaranaktif-table"]) {
                    window.LaravelDataTables["matapelajaranaktif-table"].draw();
                }
            });
        });

        $(document).on('click', '.btn-hapus', function (e) {
            e.preventDefault();
            const form = $(this).closest('form');
            const nama = $(this).data('nama');

            Swal.fire({
                title: 'Hapus Mata Pelajaran Aktif?',
                html: `Anda yakin ingin menghapus jadwal:<br><strong class="text-danger">${nama}</strong>?<br><small class="text-muted">Tindakan ini tidak dapat dibatalkan.</small>`,
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
