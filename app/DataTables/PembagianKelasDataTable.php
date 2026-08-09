<?php

namespace App\DataTables;

use App\Models\PembagianKelas;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PembagianKelasDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<PembagianKelas> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('nisn', function ($row) {
                return $row->siswa ? $row->siswa->nisn : '-';
            })
            ->addColumn('nama_siswa', function ($row) {
                return $row->siswa ? $row->siswa->nama_siswa : '-';
            })
            ->addColumn('tahun_ajaran', function ($row) {
                return $row->tahunAjaran ? $row->tahunAjaran->nama_tahun_ajaran : '-';
            })
            ->addColumn('tingkat', function ($row) {
                return $row->kelas ? $row->kelas->tingkat : '-';
            })
            ->addColumn('nama_kelas', function ($row) {
                return $row->kelas ? $row->kelas->nama_kelas : '-';
            })
            ->addColumn('wali_kelas', function ($row) {
                return $row->wali_kelas_nama;
            })
            ->addColumn('action', function ($row) {
                if (auth()->user()?->roles !== 'admin') return '';
                return '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . route('pembagiankelas.edit', $row->id) . '" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="' . route('pembagiankelas.destroy', $row->id) . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn btn-danger btn-sm btn-hapus" data-nama="' . e($row->siswa->nama_siswa ?? 'Siswa') . '" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                ';
            })
            ->filterColumn('nisn', function($query, $keyword) {
                $query->whereHas('siswa', fn($q) => $q->where('nisn', 'like', "%{$keyword}%"));
            })
            ->filterColumn('nama_siswa', function($query, $keyword) {
                $query->whereHas('siswa', fn($q) => $q->where('nama_siswa', 'like', "%{$keyword}%"));
            })
            ->filterColumn('tahun_ajaran', function($query, $keyword) {
                $query->whereHas('tahunAjaran', fn($q) => $q->where('tahun_mulai', 'like', "%{$keyword}%")
                    ->orWhere('tahun_selesai', 'like', "%{$keyword}%")
                    ->orWhereRaw("CONCAT(tahun_mulai, '/', tahun_selesai) like ?", ["%{$keyword}%"]));
            })
            ->filterColumn('tingkat', function($query, $keyword) {
                $query->whereHas('kelas', fn($q) => $q->where('tingkat', 'like', "%{$keyword}%"));
            })
            ->filterColumn('nama_kelas', function($query, $keyword) {
                $query->whereHas('kelas', fn($q) => $q->where('nama_kelas', 'like', "%{$keyword}%"));
            })
            ->filterColumn('wali_kelas', function($query, $keyword) {
                $query->whereHas('kelas.waliKelas.guru.pegawai', fn($q) => $q->where('nama_pegawai', 'like', "%{$keyword}%"));
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<PembagianKelas>
     */
    public function query(PembagianKelas $model): QueryBuilder
    {
        return $model->newQuery()->with(['siswa', 'kelas', 'tahunAjaran']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('pembagiankelas-table')
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

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $columns = [
            Column::make('DT_RowIndex')->title('No')->searchable(false)->orderable(false),
            Column::make('nisn')->title('NISN'),
            Column::make('nama_siswa')->title('Nama Siswa'),
            Column::make('tahun_ajaran')->title('Tahun Ajaran'),
            Column::make('tingkat')->title('Tingkat Kelas'),
            Column::make('nama_kelas')->title('Kelas'),
            Column::make('wali_kelas')->title('Wali Kelas'),
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
        return 'PembagianKelas_' . date('YmdHis');
    }
}
