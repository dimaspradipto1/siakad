<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor_{{ str_replace(' ', '_', $siswa->nama_siswa) }}_{{ $semester->nama_semester }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 30px;
            font-size: 13px;
            line-height: 1.3;
        }

        /* Kop Surat Styling */
        .kop-container {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        .kop-pemerintah {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 0;
        }
        .kop-dinas {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 0;
        }
        .kop-sekolah {
            font-size: 18px;
            font-weight: bold;
            margin: 2px 0;
        }
        .kop-npsn {
            font-size: 12px;
            font-weight: bold;
            margin: 0;
        }
        .kop-detail {
            font-size: 11px;
            font-style: italic;
            margin: 2px 0 0 0;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .header-table td {
            padding: 3px;
            vertical-align: top;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        table.main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table.main-table th, table.main-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            vertical-align: middle;
        }

        table.main-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }

        .text-center {
            text-align: center;
        }

        .text-start {
            text-align: left;
        }

        .fw-bold {
            font-weight: bold;
        }

        .summary-container {
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            margin-bottom: 5px;
            font-size: 13px;
        }

        .summary-label {
            width: 150px;
            font-weight: bold;
        }

        .summary-value {
            font-weight: bold;
        }

        .signature-container {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .signature-table td {
            border: none;
            padding: 5px;
            text-align: center;
            vertical-align: top;
            width: 33%;
        }

        /* Button Container and Action Buttons */
        .action-container {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            border: none;
        }

        .btn-action-kembali {
            background-color: #f8f9fa;
            color: #212529;
            border: 1px solid #ced4da;
        }

        .btn-action-kembali:hover {
            background-color: #e2e6ea;
            border-color: #dae0e5;
        }

        .btn-action-cetak {
            background-color: #212529;
            color: #ffffff;
        }

        .btn-action-cetak:hover {
            background-color: #000000;
        }

        @media print {
            .action-container {
                display: none !important;
            }
            .d-print-none {
                display: none !important;
            }
            body {
                padding: 10px;
            }
            @page {
                size: A4;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>

    @php
    if (!function_exists('terbilang')) {
        function terbilang($number) {
            $number = abs($number);
            $words = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
            $temp = "";
            if ($number < 12) {
                $temp = " " . $words[$number];
            } else if ($number < 20) {
                $temp = terbilang($number - 10) . " belas";
            } else if ($number < 100) {
                $temp = terbilang((int)($number / 10)) . " puluh" . terbilang($number % 10);
            } else if ($number < 200) {
                $temp = " seratus" . terbilang($number - 100);
            }
            return trim($temp);
        }
    }
    @endphp

    <!-- Kop Surat -->
    <div class="kop-container">
        <div class="kop-pemerintah">PEMERINTAH KOTA BATAM</div>
        <div class="kop-dinas">DINAS PENDIDIKAN</div>
        <div class="kop-sekolah">{{ strtoupper($school->nama_sekolah ?? 'SEKOLAH DASAR NEGERI 007 SEKUPANG') }}</div>
        <div class="kop-npsn">NOMOR POKOK SEKOLAH NASIONAL : {{ $school->npsn ?? '11001560' }}</div>
        <div class="kop-detail">
            Alamat : {{ $school->alamat_sekolah ?? 'Jl. Tambelan Blok II Tiban Indah Kec. Sekupang Kota Batam' }}<br>
            Telp. {{ $school->telp ?? '(0778) 324 471' }}, Fax. {{ $school->fax ?? '(0778) 324 471' }} Email: {{ $school->email ?? 'sdn7skp@gmail.com' }}
        </div>
    </div>

    <div class="title">LAPORAN HASIL BELAJAR SISWA SUMATIF</div>

    <!-- Student Info -->
    <table class="header-table">
        <tr>
            <td style="width: 18%;">Nama Siswa</td>
            <td style="width: 2%;">:</td>
            <td style="width: 40%;" class="fw-bold">{{ $siswa->nama_siswa }}</td>
            <td style="width: 15%;">Kelas</td>
            <td style="width: 2%;">:</td>
            <td style="width: 23%;">{{ $kelasModel->nama_kelas ?? '-' }}</td>
        </tr>
        <tr>
            <td>NIS/NISN</td>
            <td>:</td>
            <td>{{ $siswa->nis ?? '-' }} / {{ $siswa->nisn }}</td>
            <td>Semester</td>
            <td>:</td>
            <td>{{ $semester->nama_semester ?? '-' }}</td>
        </tr>
        <tr>
            <td>Nama Sekolah</td>
            <td>:</td>
            <td class="fw-bold">{{ $school->nama_sekolah ?? 'SD NEGERI 007 SEKUPANG' }}</td>
            <td>Tahun Ajaran</td>
            <td>:</td>
            <td>{{ $tahunAjaran->nama_tahun_ajaran ?? '-' }}</td>
        </tr>
        <tr>
            <td>Alamat Sekolah</td>
            <td>:</td>
            <td>{{ $school->alamat_sekolah ?? 'Jl. Tambelan Blok II Tiban Indah Kec. Sekupang Kota Batam' }}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <!-- Main Table -->
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 18%;">Mata Pelajaran</th>
                <th style="width: 6%;">KKM</th>
                <th style="width: 6%;">Angka</th>
                <th style="width: 16%;">Huruf</th>
                <th style="width: 8%;">Keterangan</th>
                <th style="width: 21%;">TP Yang Diukur dan Tercapai Optimal</th>
                <th style="width: 21%;">TP Yang Perlu Peningkatan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalNilai = 0;
                $countNilai = 0;
            @endphp
            @foreach($classMapels as $index => $mp)
                @php
                    $rec = $grades[$mp->id] ?? null;
                    $nilai = $rec && $rec->nilai_raport !== null ? intval($rec->nilai_raport) : null;
                    $predikat = $rec && $rec->predikat ? $rec->predikat : null;
                    
                    if ($nilai !== null) {
                        $totalNilai += $nilai;
                        $countNilai++;
                    }

                    if (!function_exists('parseTpList')) {
                        function parseTpList($value): array {
                            if (empty($value)) return [];

                            if (is_array($value)) {
                                $result = [];
                                foreach ($value as $item) {
                                    $result = array_merge($result, parseTpList($item));
                                }
                                return array_values(array_unique(array_filter($result)));
                            }

                            if (is_string($value)) {
                                $value = trim($value);
                                if ($value === '') return [];

                                $decoded = json_decode($value, true);
                                if (json_last_error() === JSON_ERROR_NONE && !is_null($decoded) && $decoded !== $value) {
                                    return parseTpList($decoded);
                                }

                                $cleaned = str_replace(['\"', '\\"'], '"', $value);
                                $cleaned = trim($cleaned, '[]"\'\\');
                                $cleaned = trim($cleaned);

                                if ($cleaned !== '') {
                                    return [$cleaned];
                                }
                            }

                            return [];
                        }
                    }

                    if (!function_exists('formatTpNumberedText')) {
                        function formatTpNumberedText($value, $fallback = '-'): string {
                            $items = parseTpList($value);
                            if (empty($items)) {
                                return $fallback;
                            }

                            $lines = [];
                            foreach ($items as $idx => $item) {
                                $lines[] = ($idx + 1) . '. ' . e($item);
                            }

                            return implode('<br>', $lines);
                        }
                    }

                    $tpOptimalSiswa = $rec->tp_optimal ?? null;
                    $tpPeningkatanSiswa = $rec->tp_perlu_peningkatan ?? null;

                    $rawOpt = $tpOptimalSiswa ?: $mp->tp_optimal;
                    $rawImp = $tpPeningkatanSiswa ?: $mp->tp_peningkatan;

                    $optText = formatTpNumberedText($rawOpt, 'Menunjukkan penguasaan kompetensi dengan sangat baik.');
                    $impText = formatTpNumberedText($rawImp, 'Perlu bimbingan lebih lanjut untuk meningkatkan kompetensi.');
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-start fw-semibold">{{ $mp->nama_mata_pelajaran }}</td>
                    <td class="text-center">{{ $mp->kkm ?? 75 }}</td>
                    <td class="text-center fw-bold">{{ $nilai !== null ? $nilai : '-' }}</td>
                    <td class="text-center" style="font-size: 11px; text-transform: capitalize;">
                        {{ $nilai !== null ? terbilang($nilai) : '-' }}
                    </td>
                    <td class="text-center fw-bold">{{ $predikat !== null ? $predikat : '-' }}</td>
                    <td class="text-start" style="font-size: 11px;">{!! $optText !!}</td>
                    <td class="text-start" style="font-size: 11px;">{!! $impText !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $rataRata = $countNilai > 0 ? round($totalNilai / $countNilai, 1) : 0;
    @endphp

    <!-- Summary Details -->
    <div class="summary-container">
        <div class="summary-row">
            <div class="summary-label">Jumlah Nilai</div>
            <div class="summary-value">: {{ $totalNilai > 0 ? $totalNilai : '-' }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Rata-Rata</div>
            <div class="summary-value">: {{ $rataRata > 0 ? $rataRata : '-' }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Rangking</div>
            <div class="summary-value">: {{ $ranking }} dari {{ $totalStudents }} siswa</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Ekstrakurikuler</div>
            <div class="summary-value">: {{ $ekskulText }}</div>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signature-container">
        <table class="signature-table">
            <tr>
                <td>
                    Mengetahui<br>
                    Orang Tua/Wali Siswa,<br><br><br><br><br>
                    <strong><u>{{ $ortuNama }}</u></strong>
                </td>
                <td>
                    <br>
                    Mengetahui,<br>
                    Kepala Sekolah<br><br><br><br>
                    <strong><u>{{ $kepsekNama }}</u></strong><br>
                    NIP. {{ $kepsekNip }}
                </td>
                <td>
                    Batam, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}<br>
                    Wali Kelas,<br><br><br><br><br>
                    <strong><u>{{ $waliKelas->guru?->pegawai?->nama_pegawai ?? '..........................................' }}</u></strong><br>
                    NIP. {{ $waliKelas->guru?->pegawai?->nip ?? '..........................................' }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Print / Back buttons footer -->
    <div class="action-container d-print-none">
        <a href="{{ route('nilai.cetak-raport') }}" class="btn-action btn-action-kembali">Kembali</a>
        <button onclick="window.print()" class="btn-action btn-action-cetak">Cetak</button>
    </div>

</body>
</html>
