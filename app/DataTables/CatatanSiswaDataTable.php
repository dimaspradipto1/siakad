<?php

namespace App\DataTables;

use App\Models\CatatanSiswa;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CatatanSiswaDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('tanggal', function ($c) {
                return \Carbon\Carbon::parse($c->tanggal)->locale('id')->translatedFormat('l, d F Y');
            })
            ->addColumn('siswa', function ($c) {
                return $c->siswa ? $c->siswa->nama_siswa : '-';
            })
            ->addColumn('kelas', function ($c) {
                return ($c->siswa && $c->siswa->kelas) ? $c->siswa->kelas->nama_kelas : '-';
            })
            ->addColumn('jenis', function ($c) {
                return $c->jenisCatatan ? $c->jenisCatatan->nama_jenis_catatan : '-';
            })
            ->addColumn('guru', function ($c) {
                return $c->guru && $c->guru->pegawai ? $c->guru->pegawai->nama_pegawai : '-';
            })
            ->addColumn('action', function ($c) {
                if (in_array(auth()->user()?->roles, ['siswa', 'orang tua', 'kepala sekolah'])) return '';
                return '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . route('catatansiswa.edit', $c->id) . '" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="' . route('catatansiswa.destroy', $c->id) . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn btn-danger btn-sm btn-hapus" data-nama="Catatan ' . e($c->siswa->nama_siswa ?? '') . '" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                ';
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(CatatanSiswa $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['siswa.kelas', 'jenisCatatan', 'guru.pegawai']);
        $user = auth()->user();
        if ($user && $user->roles === 'siswa') {
            $query->whereHas('siswa', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($user && $user->roles === 'orang tua') {
            $query->whereHas('siswa.orangTua', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($user && in_array($user->roles, ['guru', 'wali kelas'])) {
            $guruId = $user->pegawai->guru->id ?? null;
            if ($guruId) {
                $kelasIds = \App\Models\WaliKelas::where('guru_id', $guruId)->pluck('kelas_id');
                $query->whereHas('siswa', function($q) use ($kelasIds) {
                    $q->whereIn('kelas_id', $kelasIds);
                });
            }
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('catatansiswa-table')
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
            Column::make('DT_RowIndex')->title('No')->searchable(false)->orderable(false),
            Column::make('tanggal')->title('Tanggal'),
            Column::make('siswa')->title('Siswa')->searchable(false)->orderable(false),
            Column::make('kelas')->title('Kelas')->searchable(false)->orderable(false),
            Column::make('jenis')->title('Jenis Catatan')->searchable(false)->orderable(false),
            Column::make('isi_catatan')->title('Isi Catatan'),
            Column::make('guru')->title('Dilaporkan Oleh')->searchable(false)->orderable(false),
            Column::make('status')->title('Status'),
        ];

        if (!in_array(auth()->user()?->roles, ['siswa', 'orang tua', 'kepala sekolah'])) {
            $columns[] = Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(100)
                  ->addClass('text-start');
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'CatatanSiswa_' . date('YmdHis');
    }
}
