<?php

namespace App\DataTables;

use App\Models\Nilai;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class NilaiDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('siswa', function ($n) {
                return $n->siswa ? $n->siswa->nama_siswa : '-';
            })
            ->addColumn('kelas', function ($n) {
                return ($n->siswa && $n->siswa->kelas) ? $n->siswa->kelas->nama_kelas : '-';
            })
            ->addColumn('mapel', function ($n) {
                return $n->mataPelajaran ? $n->mataPelajaran->nama_mata_pelajaran : '-';
            })
            ->addColumn('semester_ta', function ($n) {
                $sem = $n->semester ? $n->semester->nama_semester : '-';
                $ta = $n->tahunAjaran ? $n->tahunAjaran->nama_tahun_ajaran : '-';
                return $sem . ' (' . $ta . ')';
            })
            ->addColumn('action', function ($n) {
                if (in_array(auth()->user()?->roles, ['siswa', 'orang tua', 'kepala sekolah'])) return '';
                return '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . route('nilai.edit', $n->id) . '" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="' . route('nilai.destroy', $n->id) . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn btn-danger btn-sm btn-hapus" data-nama="' . e($n->siswa->nama_siswa ?? 'Data') . '" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                ';
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(Nilai $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['siswa.kelas', 'mataPelajaran', 'semester', 'tahunAjaran']);
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
                    ->setTableId('nilai-table')
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
            Column::make('siswa')->title('Siswa')->searchable(false)->orderable(false),
            Column::make('kelas')->title('Kelas')->searchable(false)->orderable(false),
            Column::make('mapel')->title('Mata Pelajaran')->searchable(false)->orderable(false),
            Column::make('semester_ta')->title('Semester (TA)')->searchable(false)->orderable(false),
            Column::make('nilai')->title('Nilai'),
            Column::make('predikat')->title('Predikat'),
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
        return 'Nilai_' . date('YmdHis');
    }
}
