<?php

namespace App\DataTables;

use App\Models\Pegawai;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PegawaiDataTable extends DataTable
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
            ->addColumn('golongan', function ($pegawai) {
                return $pegawai->golongan ?? ($pegawai->guru->golongan ?? '-');
            })
            ->addColumn('pendidikan_terakhir', function ($pegawai) {
                return $pegawai->pendidikan_terakhir ?? ($pegawai->guru->pendidikan_terakhir ?? '-');
            })
            ->addColumn('status', function ($pegawai) {
                return $pegawai->status ?? 'Aktif';
            })
            ->addColumn('role', function ($pegawai) {
                if ($pegawai->user) {
                    $roles = $pegawai->user->getRolesList();
                    return implode(', ', array_map('ucwords', $roles));
                }
                return ucwords($pegawai->jabatan ?? 'Pegawai');
            })
            ->addColumn('action', function ($pegawai) {
                if (auth()->user()?->roles !== 'admin') return '';
                return '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . route('pegawai.edit', $pegawai->id) . '" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="' . route('pegawai.destroy', $pegawai->id) . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn btn-danger btn-sm btn-hapus" data-nama="' . e($pegawai->nama_pegawai) . '" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                ';
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Pegawai $model): QueryBuilder
    {
        return $model->newQuery()->with(['user', 'guru']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('pegawai-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(2)
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
            Column::make('nip')->title('NIP'),
            Column::make('nama_pegawai')->title('Nama'),
            Column::make('jenis_kelamin')->title('Jenis Kelamin'),
            Column::make('golongan')->title('Golongan')->searchable(false)->orderable(false),
            Column::make('pendidikan_terakhir')->title('Pendidikan Terakhir')->searchable(false)->orderable(false),
            Column::make('status')->title('Status')->searchable(false)->orderable(false),
            Column::make('role')->title('Role')->searchable(false)->orderable(false),
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

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Pegawai_' . date('YmdHis');
    }
}
