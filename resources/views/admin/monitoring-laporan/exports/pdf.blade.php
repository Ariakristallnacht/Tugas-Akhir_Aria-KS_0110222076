<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Monitoring Laporan Kegiatan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 20px;
        }

        p {
            margin: 0 0 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            vertical-align: top;
            text-align: left;
        }

        th {
            background: #e2e8f0;
        }

        .muted {
            color: #64748b;
        }
    </style>
</head>
<body>
    <h1>Monitoring Laporan Kegiatan</h1>
    <p>Puskesmas Bunar</p>
    <p class="muted">Periode {{ \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($filters['date_to'])->translatedFormat('d F Y') }}</p>
    <p class="muted">Diekspor pada {{ $generatedAt->translatedFormat('d F Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Pegawai</th>
                <th>Jenis</th>
                <th>Kegiatan</th>
                <th>Lokasi</th>
                <th>Waktu</th>
                <th>Status Referensi</th>
                <th>Laporan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $report)
                <tr>
                    <td>{{ $report->tanggal->translatedFormat('d/m/Y') }}</td>
                    <td>
                        {{ $report->pegawai?->nama ?? '-' }}
                        <div class="muted">{{ $report->pegawai?->jabatan ?? '-' }}</div>
                    </td>
                    <td>{{ $report->jenis_kegiatan_label }}</td>
                    <td>{{ $report->kegiatan_nama }}</td>
                    <td>{{ $report->lokasi_kegiatan }}</td>
                    <td>{{ $report->waktu_kegiatan }}</td>
                    <td>{{ $report->status_referensi }}</td>
                    <td>{{ $report->laporan }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Tidak ada data laporan pada filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
