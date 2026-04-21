@extends('layouts.dashboard')

@php
    $title = 'Dashboard PJ Penjadwalan | Puskesmas Bunar';
    $heading = 'Dashboard PJ Penjadwalan';
@endphp

@section('content')
    <section class="pkm-dashboard-grid">
        <div class="pkm-dashboard-main">
            <div class="pkm-section-head">
                <div>
                    <h2>Panel Penjadwalan</h2>
                    <p>Susun jadwal layanan, verifikasi dinas luar, dan kelola laporan kegiatan.</p>
                </div>
            </div>

            <div class="pkm-hero-panel">
                <div class="pkm-hero-panel__content">
                    <span class="pkm-hero-panel__eyebrow">PJ Penjadwalan</span>
                    <h3>Pastikan distribusi tugas layanan dan dinas luar tetap seimbang setiap hari.</h3>
                    <p>Semua aktivitas penjadwalan dipusatkan di area ini agar verifikasi dan penyusunan jadwal lebih cepat.</p>
                </div>
                <div class="pkm-hero-panel__stats">
                    <div>
                        <span>Draft Jadwal</span>
                        <strong>6</strong>
                        <small>Belum dipublikasikan</small>
                    </div>
                    <div>
                        <span>Verifikasi</span>
                        <strong>{{ $todaySubmissionCount }}</strong>
                        <small>Pengajuan baru hari ini</small>
                    </div>
                </div>
            </div>

            <section class="pkm-card pkm-table-card">
                <div class="pkm-card__head">
                    <div>
                        <h3>Fokus Hari Ini</h3>
                        <p>Kegiatan yang perlu ditindaklanjuti oleh PJ penjadwalan.</p>
                    </div>
                </div>

                <div class="pkm-table">
                    <div class="pkm-table__head">
                        <span>Pegawai</span>
                        <span>Kegiatan</span>
                        <span>Tanggal</span>
                        <span>Status</span>
                    </div>
                    @forelse ($pendingSubmissions as $submission)
                        <div class="pkm-table__row">
                            <div data-label="Pegawai">
                                <strong>{{ $submission->pegawai?->nama ?? 'Pegawai tidak ditemukan' }}</strong>
                                <small>{{ $submission->pegawai?->jabatan ?? 'Jabatan tidak tersedia' }}</small>
                            </div>
                            <div data-label="Kegiatan">
                                <strong>{{ \Illuminate\Support\Str::limit($submission->kegiatan, 40) }}</strong>
                                <small>{{ $submission->tujuan }}</small>
                            </div>
                            <div data-label="Waktu">
                                {{ $submission->tanggal_mulai->translatedFormat('d M Y') }}
                                <small>{{ $submission->tanggal_selesai->translatedFormat('d M Y') }}</small>
                            </div>
                            <div data-label="Status"><span class="pkm-pill is-amber">{{ ucfirst($submission->status) }}</span></div>
                        </div>
                    @empty
                        <div class="pkm-table__row">
                            <div data-label="Pegawai"><strong>Tidak ada pengajuan menunggu</strong><small>Semua pengajuan sudah ditindaklanjuti.</small></div>
                            <div data-label="Kegiatan">-</div>
                            <div data-label="Waktu">-</div>
                            <div data-label="Status"><span class="pkm-pill is-green">Kosong</span></div>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="pkm-dashboard-side">
            <section class="pkm-side-summary">
                <div class="pkm-side-summary__head">
                    <h3>Aksi Cepat</h3>
                </div>
                <a href="{{ route('pj.jadwal-kegiatan.index') }}" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="calendar-plus" class="size-4"></i></span>
                    <span><strong>Buat Jadwal</strong><small>Susun jadwal layanan baru.</small></span>
                </a>
                <a href="{{ route('pj.verifikasi-pengajuan-dinas.index') }}" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="shield-check" class="size-4"></i></span>
                    <span><strong>Verifikasi Dinas</strong><small>{{ $pendingCount }} pengajuan menunggu tindak lanjut.</small></span>
                </a>
            </section>
        </aside>
    </section>
@endsection
