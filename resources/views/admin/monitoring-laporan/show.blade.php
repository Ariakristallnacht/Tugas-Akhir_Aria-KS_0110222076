@extends('layouts.dashboard')

@php
    $title = $title ?? 'Detail Laporan Kegiatan | Puskesmas Bunar';
    $heading = $heading ?? 'Detail Laporan Kegiatan';
    $routeName = $routeName ?? 'admin.monitoring-laporan';
@endphp

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-card pkm-form-card">
            <div class="pkm-card__head">
                <div>
                    <h2 style="font-weight: bold">Detail Laporan Kegiatan</h2>
                    <p>{{ $report->tanggal?->translatedFormat('d F Y') ?? '-' }} - {{ $report->lokasi_kegiatan }}</p>
                </div>
            </div>

            <div class="pkm-form-grid">
                <div class="pkm-field">
                    <label>Jenis kegiatan</label>
                    <div class="pkm-input">{{ $report->jenis_kegiatan_label }}</div>
                </div>
                <div class="pkm-field">
                    <label>Kegiatan</label>
                    <div class="pkm-input">{{ $report->kegiatan_nama }}</div>
                </div>
                <div class="pkm-field">
                    <label>Pegawai pelaksana</label>
                    <div class="pkm-input">{{ $report->pegawai?->nama ?? 'Pegawai tidak ditemukan' }}</div>
                </div>
                <div class="pkm-field">
                    <label>Tanggal laporan</label>
                    <div class="pkm-input">{{ $report->tanggal?->translatedFormat('d F Y') ?? '-' }}</div>
                </div>
                <div class="pkm-field">
                    <label>Waktu / Periode kegiatan</label>
                    <div class="pkm-input">{{ $report->waktu_kegiatan }}</div>
                </div>
                <div class="pkm-field">
                    <label>Status referensi</label>
                    <div class="pkm-input">{{ $report->status_referensi }}</div>
                </div>
                <div class="pkm-field pkm-field--full">
                    <label>Referensi sumber</label>
                    @if ($report->jenis_kegiatan === \App\Models\LaporanKegiatan::JENIS_DINAS_LUAR)
                        <div class="pkm-input">
                            Tujuan: {{ $report->pengajuanDinas?->tujuan ?? '-' }}<br>
                            Kegiatan: {{ $report->pengajuanDinas?->kegiatan ?? '-' }}<br>
                            Tanggal mulai: {{ $report->pengajuanDinas?->tanggal_mulai?->translatedFormat('d F Y') ?? '-' }}<br>
                            Tanggal selesai: {{ $report->pengajuanDinas?->tanggal_selesai?->translatedFormat('d F Y') ?? '-' }}
                        </div>
                    @else
                        <div class="pkm-input">
                            Jadwal layanan: {{ $report->jadwal?->kegiatan?->nama_kegiatan ?? '-' }}<br>
                            Lokasi: {{ $report->jadwal?->lokasi ?? '-' }}<br>
                            Status jadwal: {{ ucfirst($report->jadwal?->status ?? 'tidak diketahui') }}
                        </div>
                    @endif
                </div>
                <div class="pkm-field pkm-field--full">
                    <label>Isi laporan</label>
                    <div class="pkm-input">{{ filled($report->laporan) ? $report->laporan : 'Belum ada isi laporan tertulis.' }}</div>
                </div>
                <div class="pkm-field pkm-field--full">
                    <label>Dokumen laporan</label>
                    @if ($report->dokumen_laporan_url)
                        <a href="{{ $report->dokumen_laporan_url }}" target="_blank" rel="noopener noreferrer" class="pkm-text-link" style="justify-content: flex-start;">
                            <i data-lucide="file-text" class="size-4"></i>
                            <span>{{ $report->dokumen_laporan_nama ?? 'Lihat dokumen laporan' }}</span>
                        </a>
                    @else
                        <div class="pkm-input">Belum ada dokumen laporan.</div>
                    @endif
                </div>
            </div>

            <div class="pkm-form-actions">
                <a href="{{ route($routeName) }}" class="pkm-secondary-button">
                    <i data-lucide="arrow-left" class="size-4"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </section>
@endsection
