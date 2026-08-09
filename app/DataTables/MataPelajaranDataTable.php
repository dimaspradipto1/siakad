<?php

namespace App\DataTables;

use App\Models\MataPelajaran;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class MataPelajaranDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('kode_mapel', function ($mapel) {
                return $mapel->kode_mapel ?? ('MP' . str_pad($mapel->id, 3, '0', STR_PAD_LEFT));
            })
            ->addColumn('kkm', function ($mapel) {
                return $mapel->kkm ?? 75;
            })
            ->addColumn('tp_optimal', function ($mapel) {
                return $mapel->tp_optimal ?? '-';
            })
            ->addColumn('tp_peningkatan', function ($mapel) {
                return $mapel->tp_peningkatan ?? '-';
            })
            ->addColumn('action', function ($mapel) {
                if (auth()->user()?->roles !== 'admin') return '';
                return '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . route('matapelajaran.edit', $mapel->id) . '" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="' . route('matapelajaran.destroy', $mapel->id) . '" method="POST" class="d-inline">
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
        return $model->newQuery()->whereNull('kelas_id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('matapelajaran-table')
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
            Column::make('kode_mapel')->title('Kode Mapel'),
            Column::make('nama_mata_pelajaran')->title('Nama Mapel'),
            Column::make('kkm')->title('KKM'),
            Column::make('tp_optimal')->title('TP yang Optimal'),
            Column::make('tp_peningkatan')->title('TP Yang Perlu Peningkatan'),
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
        return 'MataPelajaran_' . date('YmdHis');
    }
}
