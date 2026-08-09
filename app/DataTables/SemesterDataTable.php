<?php

namespace App\DataTables;

use App\Models\Semester;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SemesterDataTable extends DataTable
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
            ->addColumn('tahun_ajaran', function ($semester) {
                return $semester->tahun_mulai . '/' . $semester->tahun_selesai;
            })
            ->addColumn('status_tahun_ajaran', function ($semester) {
                $badge = $semester->status_ta === 'Aktif'
                    ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>'
                    : '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Tidak Aktif</span>';
                return $badge;
            })
            ->addColumn('action', function ($semester) {
                if (auth()->user()?->roles !== 'admin') return '';
                return '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . route('semester.edit', $semester->id) . '" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="' . route('semester.destroy', $semester->id) . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn btn-danger btn-sm btn-hapus" data-nama="' . e($semester->nama_semester . ' - ' . $semester->tahun_mulai . '/' . $semester->tahun_selesai) . '" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                ';
            })
            ->rawColumns(['status_tahun_ajaran', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Semester $model): QueryBuilder
    {
        return $model->newQuery()
            ->select([
                'semesters.*',
                'tahun_ajarans.tahun_mulai',
                'tahun_ajarans.tahun_selesai',
                'tahun_ajarans.status as status_ta',
            ])
            ->join('tahun_ajarans', 'semesters.tahun_ajaran_id', '=', 'tahun_ajarans.id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('semester-table')
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
            Column::make('tahun_ajaran')->title('Tahun Ajaran'),
            Column::make('nama_semester')->title('Nama Semester'),
            Column::make('status_tahun_ajaran')->title('Status TA')->searchable(false)->orderable(false),
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
        return 'Semester_' . date('YmdHis');
    }
}
