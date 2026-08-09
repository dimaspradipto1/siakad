<?php

namespace App\DataTables;

use App\Models\ProfilSekolah;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\Auth;

class ProfilSekolahDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<ProfilSekolah> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('logo', function ($row) {
                if ($row->logo_sekolah) {
                    return '<img src="' . asset($row->logo_sekolah) . '" class="img-thumbnail" style="max-height: 50px;">';
                }
                return '<span class="text-muted">Tidak ada logo</span>';
            })
            ->addColumn('kepala_sekolah_info', function ($row) {
                $info = e($row->nama_kepala_sekolah ?? '-');
                if ($row->nip_kepala_sekolah) {
                    $info .= '<br><small class="text-muted">NIP. ' . e($row->nip_kepala_sekolah) . '</small>';
                }
                return $info;
            })
            ->addColumn('tahun_ajaran', function ($row) {
                return $row->tahunAjaran ? e($row->tahunAjaran->nama_tahun_ajaran) : '-';
            })
            ->addColumn('status_badge', function ($row) {
                $statusClass = $row->status === 'Negeri' ? 'bg-success' : 'bg-primary';
                return '<span class="badge ' . $statusClass . '">' . e($row->status ?? '-') . '</span>';
            })
            ->addColumn('action', function ($row) {
                if (auth()->user()?->roles !== 'admin') return '';
                return '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . route('profil-sekolah.edit', $row->id) . '" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="' . route('profil-sekolah.destroy', $row->id) . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn btn-danger btn-sm btn-hapus" data-nama="' . e($row->nama_sekolah) . '" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                ';
            })
            ->rawColumns(['logo', 'kepala_sekolah_info', 'status_badge', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<ProfilSekolah>
     */
    public function query(ProfilSekolah $model): QueryBuilder
    {
        return $model->newQuery()->with('tahunAjaran');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('profilsekolah-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(2) // Order by School Name by default
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
            Column::computed('logo')->title('Logo')->searchable(false)->orderable(false),
            Column::make('nama_sekolah')->title('Nama Sekolah'),
            Column::make('nis_nss_nds')->title('NIS/NSS/NDS'),
            Column::computed('kepala_sekolah_info')->title('Kepala Sekolah')->orderable(false),
            Column::computed('tahun_ajaran')->title('Tahun Ajaran')->orderable(false),
            Column::computed('status_badge')->title('Status')->orderable(false),
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
        return 'ProfilSekolah_' . date('YmdHis');
    }
}
