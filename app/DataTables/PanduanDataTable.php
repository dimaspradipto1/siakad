<?php

namespace App\DataTables;

use App\Models\Panduan;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PanduanDataTable extends DataTable
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
            ->editColumn('role', function($row) {
                $icon = $row->icon ? '<i class="' . e($row->icon) . ' me-1"></i> ' : '';
                return '<div class="d-flex align-items-center gap-1 fw-semibold text-primary">' 
                    . $icon . e($row->role) 
                    . '</div>';
            })
            ->editColumn('link_gdrive', function($row) {
                if (empty($row->link_gdrive)) {
                    return '<span class="text-muted fst-italic">Belum ada link</span>';
                }
                return '<a href="' . e($row->link_gdrive) . '" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1 shadow-sm" style="font-size:12px;">
                    <i class="bi bi-google"></i> Buka G-Drive <i class="bi bi-box-arrow-up-right ms-1" style="font-size:10px;"></i>
                </a>';
            })
            ->editColumn('is_active', function($row) {
                if ($row->is_active) {
                    return '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>';
                }
                return '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Nonaktif</span>';
            })
            ->addColumn('action', function($row) {
                if (Auth::user()?->roles !== 'admin') return '';

                return '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . route('panduan.edit', $row->id) . '" class="btn btn-warning btn-sm" title="Edit Link G-Drive">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="' . route('panduan.destroy', $row->id) . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn btn-danger btn-sm btn-hapus" data-nama="' . e($row->role) . '" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                ';
            })
            ->rawColumns(['role', 'link_gdrive', 'is_active', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Panduan $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('urutan', 'asc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('panduan-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(0, 'asc')
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
            Column::make('DT_RowIndex')->title('No')->searchable(false)->orderable(false)->width(50),
            Column::make('role')->title('Role / Kategori'),
            Column::make('judul')->title('Judul Panduan'),
            Column::make('link_gdrive')->title('Link Google Drive')->orderable(false),
            Column::make('is_active')->title('Status')->addClass('text-center')->width(90),
        ];

        if (Auth::user()?->roles === 'admin') {
            $columns[] = Column::computed('action')
                  ->title('Aksi')
                  ->exportable(false)
                  ->printable(false)
                  ->width(110)
                  ->addClass('text-center');
        }

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Panduan_' . date('YmdHis');
    }
}
