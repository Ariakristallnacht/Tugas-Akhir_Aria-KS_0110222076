<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Kegiatan PJ</title>
</head>
<body>
    <table border="1">
        <tr>
            <td colspan="7"><strong>Laporan Kegiatan PJ Penjadwalan Puskesmas Bunar</strong></td>
        </tr>
        <tr>
            <td colspan="7">Periode {{ \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($filters['date_to'])->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td colspan="7">Diekspor pada {{ $generatedAt->translatedFormat('d F Y H:i') }}</td>
        </tr>
        <tr>
            <th>Tanggal</th>
            <th>Pegawai</th>
            <th>Jabatan</th>
            <th>Jenis Kegiatan</th>
            <th>Kegiatan</th>
            <th>Lokasi / Waktu</th>
            <th>Laporan</th>
        </tr>
        @forelse ($reports as $report)
            <tr>
                <td>{{ $report->tanggal->translatedFormat('d/m/Y') }}</td>
                <td>{{ $report->pegawai?->nama ?? '-' }}</td>
                <td>{{ $report->pegawai?->jabatan ?? '-' }}</td>
                <td>{{ $report->jenis_kegiatan_label }}</td>
                <td>{{ $report->kegiatan_nama }}</td>
                <td>{{ $report->lokasi_kegiatan }} / {{ $report->waktu_kegiatan }}</td>
                <td>{{ $report->laporan }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7">Tidak ada data laporan pada filter ini.</td>
            </tr>
        @endforelse
    </table>
</body>
</html>
