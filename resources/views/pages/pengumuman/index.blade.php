@extends('layouts.dashboard.template')

@section('title', 'Pengumuman')

@section('content')
    <div class="pagetitle">
        <h1 class="text-primary fw-bold">Pengumuman</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Pengumuman</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title text-dark fw-bold p-0 m-0">Daftar Pengumuman</h5>
                            @if(!in_array(auth()->user()->roles, ['siswa', 'orang tua']))
                            <a href="{{ route('pengumuman.create') }}" class="btn btn-dark btn-sm px-4 py-2" style="background-color: #212529; border-color: #212529; border-radius: 8px; font-weight: bold;">
                                Tambah Data
                            </a>
                            @endif
                        </div>
                        
                        <form id="formFilter" class="row g-4">
                            <div class="col-md-6">
                                <label for="tahun_ajaran_id" class="form-label fw-semibold text-dark">Tahun Ajaran</label>
                                <select id="tahun_ajaran_id" class="form-select py-2" style="border-radius: 8px;">
                                    <option value="" disabled selected></option>
                                    @foreach($tahunAjarans as $ta)
                                        <option value="{{ $ta->id }}">{{ $ta->nama_tahun_ajaran }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="semester_name" class="form-label fw-semibold text-dark">Semester</label>
                                <select id="semester_name" class="form-select py-2" style="border-radius: 8px;">
                                    <option value="" disabled selected></option>
                                    <option value="Semester 1 (Ganjil)">Semester 1 (Ganjil)</option>
                                    <option value="Semester 2 (Genap)">Semester 2 (Genap)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="kelas_id" class="form-label fw-semibold text-dark">Kelas</label>
                                <select id="kelas_id" class="form-select py-2" style="border-radius: 8px;">
                                    <option value="" disabled selected></option>
                                    <option value="Semua Kelas">Semua Kelas</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="nama_mata_pelajaran" class="form-label fw-semibold text-dark">Mata Pelajaran</label>
                                <select id="nama_mata_pelajaran" class="form-select py-2" style="border-radius: 8px;">
                                    <option value="" disabled selected></option>
                                    <option value="Semua Mapel">Semua Mapel</option>
                                    @foreach($mapels as $mp)
                                        <option value="{{ $mp->nama_mata_pelajaran }}">{{ $mp->nama_mata_pelajaran }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 d-flex justify-content-end align-items-center gap-4 pt-2">
                                <a href="#" id="btnReset" class="text-dark fw-bold text-decoration-none small" style="font-size: 0.95rem;">
                                    Reset
                                </a>
                                <button type="submit" class="btn btn-dark px-4 py-2" style="background-color: #212529; border-color: #212529; border-radius: 8px; font-weight: bold; font-size: 0.95rem;">
                                    Tampilkan Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <style>
                    #pengumuman-table td:nth-child(3) {
                        text-align: left !important;
                        white-space: normal !important;
                        word-break: break-word !important;
                        min-width: 250px !important;
                    }
                </style>
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body pt-4">
                        <div class="table-responsive" style="overflow: visible;">
                            {{ $dataTable->table(['class' => 'table table-bordered table-hover align-middle text-center', 'style' => 'width:100%']) }}
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
        $(document).ready(function() {
            // Apply DataTable filter parameters
            $('#pengumuman-table').on('preXhr.dt', function (e, settings, data) {
                data.tahun_ajaran_id = $('#tahun_ajaran_id').val();
                data.semester_name = $('#semester_name').val();
                data.kelas_id = $('#kelas_id').val();
                data.nama_mata_pelajaran = $('#nama_mata_pelajaran').val();
            });

            // Submit filter
            $('#formFilter').on('submit', function (e) {
                e.preventDefault();
                window.LaravelDataTables["pengumuman-table"].draw();
            });

            // Reset filter
            $('#btnReset').on('click', function(e) {
                e.preventDefault();
                $('#tahun_ajaran_id').val('').trigger('change');
                $('#semester_name').val('').trigger('change');
                $('#kelas_id').val('').trigger('change');
                $('#nama_mata_pelajaran').val('').trigger('change');
                window.LaravelDataTables["pengumuman-table"].draw();
            });
        });

        $(document).on('click', '.btn-hapus', function (e) {
            e.preventDefault();
            const form = $(this).closest('form');
            const nama = $(this).data('nama');

            Swal.fire({
                title: 'Hapus Pengumuman?',
                html: `Anda yakin ingin menghapus:<br><strong class="text-danger">${nama}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash-fill"></i> Ya, Hapus!',
                cancelButtonText: '<i class="bi bi-x-circle"></i> Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({title: 'Menghapus...', allowOutsideClick: false, didOpen: () => {Swal.showLoading()}});
                    form.submit();
                }
            });
        });
    </script>
@endpush
