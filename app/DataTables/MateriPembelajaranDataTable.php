<?php

namespace App\DataTables;

use App\Models\MateriPembelajaran;
use App\Models\Siswa;
use App\Models\OrangTua;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MateriPembelajaranDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<MateriPembelajaran> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('deskripsi_short', function ($p) {
                return Str::limit($p->deskripsi_materi, 50);
            })
            ->addColumn('tahun_ajaran', function ($p) {
                return $p->tahunAjaran ? $p->tahunAjaran->tahun_mulai . '/' . $p->tahunAjaran->tahun_selesai : '-';
            })
            ->addColumn('semester', function ($p) {
                return $p->semester ? $p->semester->nama_semester : '-';
            })
            ->addColumn('kelas', function ($p) {
                return $p->kelas ? $p->kelas->nama_kelas : '-';
            })
            ->addColumn('mata_pelajaran', function ($p) {
                return $p->mataPelajaran ? $p->mataPelajaran->nama_mata_pelajaran : '-';
            })
            ->addColumn('diupload_oleh', function ($p) {
                return $p->uploader ? $p->uploader->name : '-';
            })
            ->addColumn('tanggal_upload', function ($p) {
                return Carbon::parse($p->created_at)->locale('id')->translatedFormat('l, d F Y');
            })
            ->addColumn('action', function ($p) {
                $user = auth()->user();
                $buttons = '';

                if ($user && !in_array($user->roles, ['siswa', 'orang tua', 'kepala sekolah'])) {
                    $buttons .= '
                    <form action="' . route('materipembelajaran.destroy', $p->id) . '" method="POST" class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn-link text-dark border-0 bg-transparent btn-hapus p-0 me-2" data-nama="' . e($p->judul_materi) . '" title="Hapus" style="font-size: 0.9rem; text-decoration: none;">
                            Delete
                        </button>
                    </form>
                    <a href="' . route('materipembelajaran.edit', $p->id) . '" class="text-dark me-2" title="Edit" style="font-size: 0.9rem; text-decoration: none;">
                        Edit
                    </a>';
                }

                $buttons .= '
                <a href="' . route('materipembelajaran.download', $p->id) . '" class="text-dark" title="Download" style="font-size: 0.9rem; text-decoration: none;">
                    Download
                </a>';

                return '<div class="d-flex justify-content-center align-items-center">' . $buttons . '</div>';
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<MateriPembelajaran>
     */
    public function query(MateriPembelajaran $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['tahunAjaran', 'semester', 'kelas', 'mataPelajaran', 'uploader']);

        if ($this->request()->has('tahun_ajaran_id') && $this->request()->get('tahun_ajaran_id') != '') {
            $query->where('tahun_ajaran_id', $this->request()->get('tahun_ajaran_id'));
        }
        if ($this->request()->has('semester_name') && $this->request()->get('semester_name') != '') {
            $query->whereHas('semester', function ($q) {
                $q->where('nama_semester', $this->request()->get('semester_name'));
            });
        }
        if ($this->request()->has('kelas_id') && $this->request()->get('kelas_id') != '') {
            $query->where('kelas_id', $this->request()->get('kelas_id'));
        }
        if ($this->request()->has('nama_mata_pelajaran') && $this->request()->get('nama_mata_pelajaran') != '') {
            $query->whereHas('mataPelajaran', function ($q) {
                $q->where('nama_mata_pelajaran', $this->request()->get('nama_mata_pelajaran'));
            });
        }

        // Role-based visibility
        $user = auth()->user();
        if ($user && in_array($user->roles, ['siswa', 'orang tua'])) {
            $siswa = null;
            if ($user->roles === 'siswa') {
                $siswa = Siswa::query()->where('user_id', $user->id)->first();
            } else {
                $orangTua = OrangTua::query()->where('user_id', $user->id)->first();
                if (!$orangTua) {
                    $orangTua = OrangTua::query()->where('nama_ayah', 'like', '%' . $user->name . '%')
                        ->orWhere('nama_ibu', 'like', '%' . $user->name . '%')
                        ->first();
                }
                if ($orangTua) {
                    $selectedChildId = session('selected_child_id');
                    if ($selectedChildId) {
                        $siswa = Siswa::where('id', $selectedChildId)->where('orang_tua_id', $orangTua->id)->first();
                    }
                    if (!$siswa) {
                        $siswa = Siswa::where('orang_tua_id', $orangTua->id)->first();
                    }
                }
            }

            if ($siswa) {
                $studentKelasIds = \App\Models\PembagianKelas::where('siswa_id', $siswa->id)->pluck('kelas_id')->toArray();
                if ($siswa->kelas_id) {
                    $studentKelasIds[] = $siswa->kelas_id;
                }
                $studentKelasIds = array_unique(array_filter($studentKelasIds));
                $query->whereIn('kelas_id', $studentKelasIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('materipembelajaran-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax('', 'data.tahun_ajaran_id = $("#tahun_ajaran_id").val(); data.semester_name = $("#semester_name").val(); data.kelas_id = $("#kelas_id").val(); data.nama_mata_pelajaran = $("#nama_mata_pelajaran").val();')
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
        return [
            Column::make('DT_RowIndex')->title('No')->searchable(false)->orderable(false),
            Column::make('judul_materi')->title('Judul Materi'),
            Column::make('deskripsi_short')->title('Deskripsi Materi')->searchable(false)->orderable(false),
            Column::make('tahun_ajaran')->title('Tahun Ajaran')->searchable(false)->orderable(false),
            Column::make('semester')->title('Semester')->searchable(false)->orderable(false),
            Column::make('kelas')->title('Kelas')->searchable(false)->orderable(false),
            Column::make('diupload_oleh')->title('Dipublikasi Oleh')->searchable(false)->orderable(false),
            Column::make('tanggal_upload')->title('Tanggal Publikasi')->searchable(false)->orderable(false),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(120)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'MateriPembelajaran_' . date('YmdHis');
    }
}
