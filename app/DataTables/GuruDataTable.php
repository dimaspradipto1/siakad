<?php

namespace App\DataTables;

use App\Models\Guru;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class GuruDataTable extends DataTable
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
            ->addColumn('status', function ($guru) {
                return $guru->status ?? 'Aktif';
            })
            ->addColumn('action', function ($guru) {
                if (auth()->user()?->roles !== 'admin') return '';
                return '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . route('guru.edit', $guru->id) . '" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="' . route('guru.destroy', $guru->id) . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn btn-danger btn-sm btn-hapus" data-nama="' . e($guru->nama_pegawai ?? $guru->nip_guru) . '" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                ';
            })
            ->filterColumn('nama_pegawai', function ($query, $keyword) {
                $query->whereRaw('LOWER(pegawais.nama_pegawai) LIKE ?', ['%' . strtolower($keyword) . '%']);
            })
            ->filterColumn('jenis_kelamin', function ($query, $keyword) {
                $query->whereRaw('LOWER(pegawais.jenis_kelamin) LIKE ?', ['%' . strtolower($keyword) . '%']);
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Guru $model): QueryBuilder
    {
        return $model->newQuery()
            ->select([
                'gurus.*',
                'pegawais.nama_pegawai as nama_pegawai',
                'pegawais.jenis_kelamin as jenis_kelamin',
                'pegawais.golongan as golongan',
                'pegawais.pendidikan_terakhir as pendidikan_terakhir',
                'pegawais.status as status'
            ])
            ->join('pegawais', 'gurus.pegawai_id', '=', 'pegawais.id')
            ->join('users', 'pegawais.user_id', '=', 'users.id')
            ->whereRaw('LOWER(users.roles) LIKE ?', ['%guru%']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('guru-table')
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
            Column::make('DT_RowIndex')->title('No')->searchable(false)->orderable(false)->addClass('text-center'),
            Column::make('nip_guru')->title('NIP Guru'),
            Column::make('nama_pegawai')->title('Nama Guru'),
            Column::make('jenis_kelamin')->title('Jenis Kelamin'),
            Column::make('golongan')->title('Golongan'),
            Column::make('pendidikan_terakhir')->title('Pendidikan Terakhir'),
            Column::make('status')->title('Status')->searchable(false)->orderable(false),
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
        return 'Guru_' . date('YmdHis');
    }
}
