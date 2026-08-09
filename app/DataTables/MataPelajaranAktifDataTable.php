<?php

namespace App\DataTables;

use App\Models\MataPelajaran;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class MataPelajaranAktifDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('nama_kelas', function ($mapel) {
                return $mapel->kelas?->nama_kelas ?? '-';
            })
            ->addColumn('nama_tahun_ajaran', function ($mapel) {
                return $mapel->tahunAjaran ? ($mapel->tahunAjaran->tahun_mulai . '/' . $mapel->tahunAjaran->tahun_selesai) : '-';
            })
            ->addColumn('nama_semester', function ($mapel) {
                return $mapel->semester?->nama_semester ?? '-';
            })
            ->addColumn('nama_guru', function ($mapel) {
                return $mapel->guru?->pegawai?->nama_pegawai ?? '-';
            })
            ->addColumn('action', function ($mapel) {
                if (auth()->user()?->roles !== 'admin') return '';
                return '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . route('matapelajaranaktif.edit', $mapel->id) . '" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="' . route('matapelajaranaktif.destroy', $mapel->id) . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn btn-danger btn-sm btn-hapus" data-nama="' . e($mapel->nama_mata_pelajaran) . '" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                ';
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(MataPelajaran $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['kelas', 'tahunAjaran', 'semester', 'guru.pegawai'])
            ->whereNotNull('mata_pelajarans.kelas_id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('matapelajaranaktif-table')
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
            Column::make('DT_RowIndex')->title('No')->searchable(false)->orderable(false)->addClass('text-center'),
            Column::make('nama_kelas')->title('Kelas'),
            Column::make('nama_tahun_ajaran')->title('Tahun Ajaran'),
            Column::make('nama_semester')->title('Semester'),
            Column::make('nama_mata_pelajaran')->title('Nama Mapel'),
            Column::make('nama_guru')->title('Nama Guru'),
            Column::make('hari_mengajar')->title('Hari Mengajar'),
            Column::make('jam_mengajar')->title('Jam Mengajar'),
        ];

        if (auth()->user()?->roles === 'admin') {
            $columns[] = Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(100)
                  ->addClass('text-center');
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'MataPelajaranAktif_' . date('YmdHis');
    }
}
