<?php

namespace App\DataTables;

use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TahunAjaranDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('nama_tahun_ajaran', function ($tahunAjaran) {
                return $tahunAjaran->tahun_mulai . '/' . $tahunAjaran->tahun_selesai;
            })
            ->addColumn('status_badge', function ($tahunAjaran) {
                $badge = $tahunAjaran->status === 'Aktif'
                    ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>'
                    : '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Tidak Aktif</span>';
                return $badge;
            })
            ->addColumn('action', function ($tahunAjaran) {
                if (auth()->user()?->roles !== 'admin') return '';
                return '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . route('tahun-ajaran.edit', $tahunAjaran->id) . '" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="' . route('tahun-ajaran.destroy', $tahunAjaran->id) . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn btn-danger btn-sm btn-hapus" data-nama="' . e($tahunAjaran->tahun_mulai . '/' . $tahunAjaran->tahun_selesai) . '" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                ';
            })
            ->rawColumns(['status_badge', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(TahunAjaran $model): QueryBuilder
    {
        return $model->newQuery()->select('tahun_ajarans.*');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('tahun-ajaran-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(1) // Urutkan berdasarkan Tahun Ajaran
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

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $columns = [
            Column::make('DT_RowIndex')->title('No')->searchable(false)->orderable(false),
            Column::make('nama_tahun_ajaran')->title('Tahun Ajaran')->orderable(true),
            Column::make('tahun_mulai')->title('Tahun Mulai'),
            Column::make('tahun_selesai')->title('Tahun Selesai'),
            Column::make('status_badge')->title('Status')->searchable(false)->orderable(false),
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

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'TahunAjaran_' . date('YmdHis');
    }
}
