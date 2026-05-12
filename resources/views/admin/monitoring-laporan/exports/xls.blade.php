<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Monitoring Laporan Kegiatan</title>
</head>
<body>
    <table border="1">
        <tr>
            <td colspan="8"><strong>Monitoring Laporan Kegiatan Puskesmas Bunar</strong></td>
        </tr>
        <tr>
            <td colspan="8">Periode {{ \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($filters['date_to'])->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td colspan="8">Diekspor pada {{ $generatedAt->translatedFormat('d F Y H:i') }}</td>
        </tr>
        <tr>
            <th>Tanggal</th>
            <th>Pegawai</th>
            <th>Jabatan</th>
            <th>Jenis Kegiatan</th>
            <th>Kegiatan</th>
            <th>Lokasi</th>
            <th>Waktu / Status Referensi</th>
            <th>Laporan</th>
        </tr>
        @forelse ($reports as $report)
            <tr>
                <td>{{ $report->tanggal->translatedFormat('d/m/Y') }}</td>
                <td>{{ $report->pegawai?->nama ?? '-' }}</td>
                <td>{{ $report->pegawai?->jabatan ?? '-' }}</td>
                <td>{{ $report->jenis_kegiatan_label }}</td>
                <td>{{ $report->kegiatan_nama }}</td>
                <td>{{ $report->lokasi_kegiatan }}</td>
                <td>{{ $report->waktu_kegiatan }} / {{ $report->status_referensi }}</td>
                <td>{{ $report->laporan }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8">Tidak ada data laporan pada filter ini.</td>
            </tr>
        @endforelse
    </table>
</body>
</html>
