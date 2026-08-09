<?php

namespace App\DataTables;

use App\Models\WaliKelas;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class WaliKelasDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('guru_nama', function ($waliKelas) {
                return $waliKelas->guru->pegawai->nama_pegawai ?? '-';
            })
            ->addColumn('kelas_nama', function ($waliKelas) {
                return $waliKelas->kelas->nama_kelas ?? '-';
            })
            ->addColumn('tahun_ajaran', function ($waliKelas) {
                return $waliKelas->tahunAjaran ? $waliKelas->tahunAjaran->nama_tahun_ajaran : '-';
            })
            ->addColumn('action', function ($waliKelas) {
                if (!in_array(auth()->user()->roles, ['admin', 'kepala sekolah'])) return '';
                return '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . route('walikelas.edit', $waliKelas->id) . '" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="' . route('walikelas.destroy', $waliKelas->id) . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn btn-danger btn-sm btn-hapus" data-nama="Wali Kelas ' . e($waliKelas->kelas->nama_kelas ?? '') . ' (' . e($waliKelas->guru->pegawai->nama_pegawai ?? '') . ')" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                ';
            })
            ->filterColumn('guru_nama', function($query, $keyword) {
                $query->whereHas('guru.pegawai', function($q) use ($keyword) {
                    $q->where('nama_pegawai', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('kelas_nama', function($query, $keyword) {
                $query->whereHas('kelas', function($q) use ($keyword) {
                    $q->where('nama_kelas', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('tahun_ajaran', function($query, $keyword) {
                $query->whereHas('tahunAjaran', function($q) use ($keyword) {
                    $q->where('tahun_ajaran', 'like', "%{$keyword}%")
                      ->orWhere('tahun_mulai', 'like', "%{$keyword}%")
                      ->orWhere('tahun_selesai', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(WaliKelas $model): QueryBuilder
    {
        return $model->newQuery()->with(['guru.pegawai', 'kelas', 'tahunAjaran']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('walikelas-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    ]);
    }

    public function getColumns(): array
    {
        $columns = [
            Column::make('DT_RowIndex')->title('No')->searchable(false)->orderable(false),
            Column::make('tahun_ajaran')->title('Tahun Ajaran')->searchable(false)->orderable(false),
            Column::make('kelas_nama')->title('Kelas')->searchable(false)->orderable(false),
            Column::make('guru_nama')->title('Nama Wali Kelas')->searchable(false)->orderable(false),
        ];

        if (auth()->user()?->roles === 'admin') {
            $columns[] = Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(100)
                  ->addClass('text-start');
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'WaliKelas_' . date('YmdHis');
    }
}
