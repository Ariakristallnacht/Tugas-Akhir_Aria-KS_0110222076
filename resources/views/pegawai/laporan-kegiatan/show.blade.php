@extends('layouts.dashboard')

@php
    $title = 'Detail Laporan Saya | Puskesmas Bunar';
    $heading = 'Detail Laporan Saya';
@endphp

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-card pkm-form-card">
            <div class="pkm-card__head">
                <div>
                    <h2 style="font-weight: bold">Detail Laporan Saya</h2>
                    <p>{{ $report->tanggal?->translatedFormat('d F Y') ?? '-' }} - {{ $report->jadwal?->lokasi ?? 'Lokasi belum diisi' }}</p>
                </div>
            </div>

            <div class="pkm-form-grid">
                <div class="pkm-field">
                    <label>Kegiatan</label>
                    <div class="pkm-input">{{ $report->jadwal?->kegiatan?->nama_kegiatan ?? 'Kegiatan tidak ditemukan' }}</div>
                </div>
                <div class="pkm-field">
                    <label>Tanggal laporan</label>
                    <div class="pkm-input">{{ $report->tanggal?->translatedFormat('d F Y') ?? '-' }}</div>
                </div>
                <div class="pkm-field">
                    <label>Lokasi</label>
                    <div class="pkm-input">{{ $report->jadwal?->lokasi ?? 'Lokasi belum diisi' }}</div>
                </div>
                <div class="pkm-field">
                    <label>Waktu jadwal</label>
                    <div class="pkm-input">{{ $report->jadwal?->waktu_mulai?->format('H:i') ?? '-' }} - {{ $report->jadwal?->waktu_selesai?->format('H:i') ?? '-' }}</div>
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
                <a href="{{ route('pegawai.laporan-kegiatan.index') }}" class="pkm-secondary-button">
                    <i data-lucide="arrow-left" class="size-4"></i>
                    <span>Kembali</span>
                </a>
                <a href="{{ route('pegawai.laporan-kegiatan.edit', $report) }}" class="pkm-primary-button">
                    <i data-lucide="pencil" class="size-4"></i>
                    <span>Edit</span>
                </a>
            </div>
        </div>
    </section>
@endsection
