<?php

namespace App\DataTables;

use App\Models\Kelas;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class KelasDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('kode_kelas', function ($kelas) {
                return $kelas->kode_kelas ?? ('KLS-' . str_pad($kelas->id, 2, '0', STR_PAD_LEFT));
            })
            ->addColumn('action', function ($kelas) {
                if (auth()->user()?->roles !== 'admin') return '';
                return '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . route('kelas.edit', $kelas->id) . '" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="' . route('kelas.destroy', $kelas->id) . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn btn-danger btn-sm btn-hapus" data-nama="' . e($kelas->nama_kelas) . '" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                ';
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(Kelas $model): QueryBuilder
    {
        return $model->newQuery()->select('kelas.*');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('kelas-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(2) // Urutkan berdasarkan nama_kelas
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
            Column::make('kode_kelas')->title('Kode Kelas'),
            Column::make('nama_kelas')->title('Nama Kelas'),
            Column::make('tingkat')->title('Tingkat'),
            Column::make('ruangan')->title('Ruangan'),
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
        return 'Kelas_' . date('YmdHis');
    }
}
