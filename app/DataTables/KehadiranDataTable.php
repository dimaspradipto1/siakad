<?php

namespace App\DataTables;

use App\Models\Kehadiran;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class KehadiranDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('tanggal', function ($k) {
                return \Carbon\Carbon::parse($k->tanggal)->locale('id')->translatedFormat('l, d F Y');
            })
            ->addColumn('siswa', function ($k) {
                return $k->siswa ? $k->siswa->nama_siswa : '-';
            })
            ->addColumn('kelas', function ($k) {
                return ($k->siswa && $k->siswa->kelas) ? $k->siswa->kelas->nama_kelas : '-';
            })
            ->addColumn('mapel', function ($k) {
                return $k->mataPelajaran ? $k->mataPelajaran->nama_mata_pelajaran : '-';
            })
            ->addColumn('status', function ($k) {
                return $k->jenisKehadiran ? $k->jenisKehadiran->nama_kehadiran : '-';
            })
            ->addColumn('action', function ($k) {
                if (in_array(auth()->user()?->roles, ['siswa', 'orang tua', 'kepala sekolah'])) return '';
                return '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . route('kehadiran.edit', $k->id) . '" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="' . route('kehadiran.destroy', $k->id) . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn btn-danger btn-sm btn-hapus" data-nama="' . e($k->siswa->nama_siswa ?? 'Data') . '" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                ';
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(Kehadiran $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['siswa.kelas', 'mataPelajaran', 'jenisKehadiran']);
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
                    ->setTableId('kehadiran-table')
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
            Column::make('mapel')->title('Mata Pelajaran')->searchable(false)->orderable(false),
            Column::make('status')->title('Status Kehadiran')->searchable(false)->orderable(false),
            Column::make('keterangan')->title('Keterangan'),
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
        return 'Kehadiran_' . date('YmdHis');
    }
}
