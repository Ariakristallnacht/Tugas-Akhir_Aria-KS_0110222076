<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Jadwal Kegiatan Pegawai</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 11px;
        }

        h1 {
            margin: 0 0 4px;
            font-size: 18px;
            text-align: center;
        }

        p {
            margin: 0 0 6px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            vertical-align: top;
            text-align: left;
        }

        th {
            background: #f1f5f9;
            font-weight: bold;
        }

        .muted {
            color: #64748b;
            font-size: 9px;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Jadwal Kegiatan Pegawai</h1>
    <p>Puskesmas Bunar</p>
    <p class="muted" style="text-align: center;">
        Periode {{ \Carbon\Carbon::parse($date_from)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($date_to)->translatedFormat('d F Y') }}
    </p>
    <p class="muted" style="text-align: center; margin-bottom: 10px;">
        Diekspor pada {{ $generatedAt->translatedFormat('d F Y H:i') }}
    </p>

    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="15%">Tanggal</th>
                <th width="10%">Waktu</th>
                <th width="15%">Jenis Kegiatan</th>
                <th width="20%">Kegiatan</th>
                <th width="15%">Lokasi / Tujuan</th>
                <th width="20%">Pegawai Terlibat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item['date_label'] }}</td>
                    <td>{{ $item['time_label'] }}</td>
                    <td>{{ $item['type_label'] }}</td>
                    <td>
                        <strong>{{ $item['title'] }}</strong>
                        @if (!empty($item['description']))
                            <div class="muted">{{ $item['description'] }}</div>
                        @endif
                    </td>
                    <td>{{ $item['subtitle'] }}</td>
                    <td>{{ $item['people'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada jadwal kegiatan pada rentang tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
