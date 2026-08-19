<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
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
            ->editColumn('roles', function($user) {
                $roleColors = [
                    'admin'          => 'danger',
                    'kepala sekolah' => 'dark',
                    'guru'           => 'primary',
                    'wali kelas'     => 'info',
                    'pegawai'        => 'secondary',
                    'siswa'          => 'success',
                    'orang tua'      => 'warning',
                ];
                $badges = [];
                foreach ($user->getRolesList() as $r) {
                    $color = $roleColors[$r] ?? 'secondary';
                    $badges[] = '<span class="badge bg-' . $color . ' text-capitalize me-1">' . e($r) . '</span>';
                }
                return implode('', $badges);
            })
            ->editColumn('is_active', function($user) {
                if ($user->is_active) {
                    return '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>';
                }
                return '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Nonaktif</span>';
            })
            ->addColumn('action', function($user) {
                if (auth()->user()?->roles !== 'admin') return '';
                return '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . route('user.edit', $user->id) . '" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="' . route('user.destroy', $user->id) . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn btn-danger btn-sm btn-hapus" data-nama="' . e($user->name) . '" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                ';
            })
            ->rawColumns(['roles', 'is_active', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('user-table')
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
            Column::make('name')->title('Nama'),
            Column::make('email')->title('Email'),
            Column::make('roles')->title('Role'),
            Column::make('is_active')->title('Status')->addClass('text-start'),
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
        return 'User_' . date('YmdHis');
    }
}
