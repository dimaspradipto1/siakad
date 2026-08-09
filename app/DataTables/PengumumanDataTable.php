<?php

namespace App\DataTables;

use App\Models\Pengumuman;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Str;

class PengumumanDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('user', function ($p) {
                return $p->user ? $p->user->name : '-';
            })
            ->addColumn('keterangan_short', function ($p) {
                return Str::limit($p->keterangan, 50);
            })
            ->addColumn('created_at', function ($p) {
                return \Carbon\Carbon::parse($p->created_at)->locale('id')->translatedFormat('l, d F Y');
            })
            ->addColumn('tahun_ajaran', function ($p) {
                return $p->tahunAjaran ? $p->tahunAjaran->tahun_mulai . '/' . $p->tahunAjaran->tahun_selesai : '-';
            })
            ->addColumn('semester', function ($p) {
                return $p->semester ? $p->semester->nama_semester : '-';
            })
            ->addColumn('kelas', function ($p) {
                return $p->kelas ? $p->kelas->nama_kelas : 'Semua Kelas';
            })
            ->addColumn('mata_pelajaran', function ($p) {
                return $p->mataPelajaran ? $p->mataPelajaran->nama_mata_pelajaran : 'Semua Mapel';
            })
            ->addColumn('action', function ($p) {
                if (in_array(auth()->user()?->roles, ['siswa', 'orang tua', 'kepala sekolah'])) {
                    return '';
                }
                return '
                <div class="d-flex gap-2 justify-content-center">
                    <form action="' . route('pengumuman.destroy', $p->id) . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn-link text-dark border-0 bg-transparent btn-hapus p-0" data-nama="' . e($p->judul) . '" title="Hapus" style="font-size: 0.9rem; text-decoration: none;">
                            Delete
                        </button>
                    </form>
                    <a href="' . route('pengumuman.edit', $p->id) . '" class="text-dark" title="Edit" style="font-size: 0.9rem; text-decoration: none;">
                        Edit
                    </a>
                </div>
                ';
            })
            ->filterColumn('user', function($query, $keyword) {
                $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$keyword}%"));
            })
            ->filterColumn('tahun_ajaran', function($query, $keyword) {
                $query->whereHas('tahunAjaran', fn($q) => $q->where('tahun_mulai', 'like', "%{$keyword}%")->orWhere('tahun_selesai', 'like', "%{$keyword}%"));
            })
            ->filterColumn('semester', function($query, $keyword) {
                $query->whereHas('semester', fn($q) => $q->where('nama_semester', 'like', "%{$keyword}%"));
            })
            ->filterColumn('kelas', function($query, $keyword) {
                $query->whereHas('kelas', fn($q) => $q->where('nama_kelas', 'like', "%{$keyword}%"));
            })
            ->filterColumn('mata_pelajaran', function($query, $keyword) {
                $query->whereHas('mataPelajaran', fn($q) => $q->where('nama_mata_pelajaran', 'like', "%{$keyword}%"));
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(Pengumuman $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['user', 'tahunAjaran', 'semester', 'kelas', 'mataPelajaran']);
        $user = auth()->user();

        if ($user && in_array($user->roles, ['siswa', 'orang tua'])) {
            $siswa = null;
            if ($user->roles === 'siswa') {
                $siswa = \App\Models\Siswa::where('user_id', $user->id)->first();
            } else {
                $ortu = \App\Models\OrangTua::where('user_id', $user->id)->first();
                if (!$ortu) {
                    $ortu = \App\Models\OrangTua::where('nama_ayah', 'like', '%' . $user->name . '%')
                        ->orWhere('nama_ibu', 'like', '%' . $user->name . '%')
                        ->first();
                }
                if ($ortu) {
                    $selectedChildId = session('selected_child_id');
                    if ($selectedChildId) {
                        $siswa = \App\Models\Siswa::where('id', $selectedChildId)->where('orang_tua_id', $ortu->id)->first();
                    }
                    if (!$siswa) {
                        $siswa = \App\Models\Siswa::where('orang_tua_id', $ortu->id)->first();
                    }
                }
            }

            if ($siswa) {
                $studentKelasIds = \App\Models\PembagianKelas::where('siswa_id', $siswa->id)
                    ->pluck('kelas_id')
                    ->toArray();
                if ($siswa->kelas_id) {
                    $studentKelasIds[] = $siswa->kelas_id;
                }
                $studentKelasIds = array_unique(array_filter($studentKelasIds));

                $query->where(function ($q) use ($studentKelasIds) {
                    $q->whereNull('kelas_id')
                      ->orWhereIn('kelas_id', $studentKelasIds);
                });
            } else {
                $query->whereNull('kelas_id');
            }
        }
        
        $request = request();
        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }
        if ($request->filled('semester_name')) {
            $semName = $request->semester_name;
            $query->whereHas('semester', function ($q) use ($semName) {
                $q->where('nama_semester', $semName);
            });
        }
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('nama_mata_pelajaran')) {
            $mapelName = $request->nama_mata_pelajaran;
            $query->whereHas('mataPelajaran', function($q) use ($mapelName) {
                $q->where('nama_mata_pelajaran', $mapelName);
            });
        }
        
        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('pengumuman-table')
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
            Column::make('judul')->title('Judul'),
            Column::make('keterangan_short')->title('Isi Pengumuman')->searchable(false)->orderable(false),
            Column::make('user')->title('Dipublikasikan Oleh')->orderable(false),
            Column::make('created_at')->title('Tanggal Dibuat'),
            Column::make('tahun_ajaran')->title('Tahun Ajaran')->orderable(false),
            Column::make('semester')->title('Semester')->orderable(false),
            Column::make('kelas')->title('Kelas')->orderable(false),
            Column::make('mata_pelajaran')->title('Mapel')->orderable(false),
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
        return 'Pengumuman_' . date('YmdHis');
    }
}
