<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap_Kehadiran_{{ $kelasModel->nama_kelas ?? 'Kelas' }}_{{ $mapelModel->nama_mata_pelajaran ?? 'Mapel' }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 30px;
            font-size: 12px;
            line-height: 1.3;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .header-table td {
            padding: 3px 4px;
            vertical-align: top;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .subtitle {
            text-align: center;
            font-size: 13px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        table.main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.main-table th, table.main-table td {
            border: 1px solid #000;
            padding: 5px 3px;
            vertical-align: middle;
        }

        table.main-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
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

        .signature-container {
            width: 100%;
            margin-top: 30px;
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
            width: 50%;
        }

        .print-btn-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }

        .btn-print {
            background-color: #212529;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.15);
        }

        @media print {
            .print-btn-container {
                display: none;
            }
            body {
                padding: 10px;
            }
            @page {
                size: A4 landscape;
                margin: 1cm;
            }
        }
    </style>
</head>
<body>

    <div class="print-btn-container text-end">
        <button class="btn-print" onclick="window.print()">Cetak</button>
    </div>

    <div class="title">REKAPITULASI KEHADIRAN SISWA</div>
    <div class="subtitle">MATA PELAJARAN: {{ strtoupper($mapelModel->nama_mata_pelajaran ?? '-') }}</div>

    <table class="header-table">
        <tr>
            <td style="width: 15%;">Nama Sekolah</td>
            <td style="width: 2%;">:</td>
            <td style="width: 43%;" class="fw-bold">{{ $school->nama_sekolah ?? 'SD Negeri 007 Sekupang' }}</td>
            <td style="width: 15%;">Kelas</td>
            <td style="width: 2%;">:</td>
            <td style="width: 23%;">{{ $kelasModel->nama_kelas ?? '-' }}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>:</td>
            <td>{{ $school->alamat_sekolah ?? 'Sekupang, Kota Batam' }}</td>
            <td>Semester</td>
            <td>:</td>
            <td>{{ $selectedSemName ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tahun Ajaran</td>
            <td>:</td>
            <td class="fw-bold">{{ $tahunAjaran->nama_tahun_ajaran ?? '-' }}</td>
            <td>Bulan</td>
            <td>:</td>
            <td class="fw-bold">{{ $selectedBulanName ?? 'Semua Bulan' }}</td>
        </tr>
        <tr>
            <td>Mata Pelajaran</td>
            <td>:</td>
            <td class="fw-bold" colspan="4">{{ $mapelModel->nama_mata_pelajaran ?? '-' }}</td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 12%;">NISN</th>
                <th class="text-start">Nama Siswa</th>
                @foreach($dates as $d)
                    <th style="width: 35px;">{{ \Carbon\Carbon::parse($d)->format('d/m') }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $siswa)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $siswa->nisn }}</td>
                    <td class="text-start fw-semibold">{{ $siswa->nama_siswa }}</td>
                    @foreach($dates as $d)
                        @php
                            $rec = $attendanceMatrix[$siswa->id][$d] ?? null;
                            $stName = $rec?->jenisKehadiran?->nama_kehadiran;
                        @endphp
                        <td class="text-center">
                            @if($stName === 'Hadir')
                                H
                            @elseif($stName === 'Sakit')
                                S
                            @elseif($stName === 'Izin')
                                I
                            @elseif($stName === 'Alpa' || $stName === 'Tanpa Keterangan')
                                A
                            @elseif($stName)
                                {{ strtoupper(substr($stName, 0, 1)) }}
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="font-size: 11px; margin-bottom: 15px;">
        <strong>Keterangan:</strong> H = Hadir, S = Sakit, I = Izin, A = Alpa
    </div>

    <div class="signature-container">
        <table class="signature-table">
            <tr>
                <td>
                    Mengetahui,<br>
                    Kepala Sekolah<br><br><br><br><br>
                    <strong><u>{{ $school->nama_kepala_sekolah ?? '..........................................' }}</u></strong><br>
                    NIP. {{ $school->nip_kepala_sekolah ?? '..........................................' }}
                </td>
                <td>
                    Batam, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}<br>
                    Wali Kelas,<br><br><br><br><br>
                    <strong><u>{{ $waliKelas->guru->pegawai->nama_pegawai ?? '..........................................' }}</u></strong><br>
                    NIP. {{ $waliKelas->guru->pegawai->nip ?? '..........................................' }}
                </td>
            </tr>
        </table>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>

