<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Monitoring Laporan Kegiatan</title>
</head>
<body>
    <table border="1">
        <tr>
            <td colspan="7"><strong>Monitoring Laporan Kegiatan Puskesmas Bunar</strong></td>
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
            <th>Kegiatan</th>
            <th>Lokasi</th>
            <th>Waktu / Status Jadwal</th>
            <th>Laporan</th>
        </tr>
        @forelse ($reports as $report)
            <tr>
                <td>{{ $report->tanggal->translatedFormat('d/m/Y') }}</td>
                <td>{{ $report->pegawai?->nama ?? '-' }}</td>
                <td>{{ $report->pegawai?->jabatan ?? '-' }}</td>
                <td>{{ $report->jadwal?->kegiatan?->nama_kegiatan ?? 'Kegiatan tidak ditemukan' }}</td>
                <td>{{ $report->jadwal?->lokasi ?? '-' }}</td>
                <td>{{ $report->jadwal?->waktu_mulai?->format('H:i') ?? '-' }} - {{ $report->jadwal?->waktu_selesai?->format('H:i') ?? '-' }} / {{ ucfirst($report->jadwal?->status ?? 'tidak diketahui') }}</td>
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
