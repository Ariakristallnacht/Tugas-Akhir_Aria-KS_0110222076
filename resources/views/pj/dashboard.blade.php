@extends('layouts.dashboard')

@php
    $title = 'Dashboard PJ| Puskesmas Bunar';
    $heading = 'Dashboard';
@endphp

@section('content')
    <section class="pkm-dashboard-grid">
        <div class="pkm-dashboard-main">
            <div class="pkm-section-head">
                <div>
                    <h2 style="font-weight: bold">Dashboard</h2>
                </div>
            </div>

            <div class="pkm-management-summary">
                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-amber-100 text-amber-700"><i data-lucide="shield-check" class="size-5"></i></div>
                    <div class="pkm-metric-card__value">{{ $pendingCount }}</div>
                    <div class="pkm-metric-card__label">Menunggu Verifikasi</div>
                </article>
                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-cyan-100 text-cyan-700"><i data-lucide="calendar-days" class="size-5"></i></div>
                    <div class="pkm-metric-card__value">{{ $todaySubmissionCount }}</div>
                    <div class="pkm-metric-card__label">Pengajuan Hari Ini</div>
                </article>
                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-emerald-100 text-emerald-700"><i data-lucide="file-text" class="size-5"></i></div>
                    <div class="pkm-metric-card__value">{{ $reportCount }}</div>
                    <div class="pkm-metric-card__label">Total Laporan</div>
                </article>
            </div>

            <section class="pkm-card pkm-table-card">
                <div class="pkm-card__head">
                    <div>
                        <h3 style="font-weight: bold">Fokus Hari Ini</h3>
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
                            <div data-label="Pegawai"><strong>Tidak ada pengajuan</strong><small>-</small></div>
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
                    <h3 style="font-weight: bold">Aksi Cepat</h3>
                </div>
                <a href="{{ route('pj.jadwal-kegiatan.index') }}" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="calendar-plus" class="size-4"></i></span>
                    <span><strong>Buat Jadwal</strong><small>Susun jadwal layanan baru.</small></span>
                </a>
                <a href="{{ route('pj.verifikasi-pengajuan-dinas.index') }}" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="shield-check" class="size-4"></i></span>
                    <span><strong>Verifikasi Dinas</strong><small>{{ $pendingCount }} pengajuan menunggu tindak lanjut.</small></span>
                </a>
                <a href="{{ route('pj.kegiatan.index') }}" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="folders" class="size-4"></i></span>
                    <span><strong>Kelola Layanan</strong><small>Atur layanan poli yang dipakai penjadwalan.</small></span>
                </a>
                <a href="{{ route('pj.monitoring-laporan') }}" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="file-spreadsheet" class="size-4"></i></span>
                    <span><strong>Monitoring Laporan</strong><small>Pantau laporan kegiatan yang dikirim pegawai.</small></span>
                </a>
            </section>
        </aside>
    </section>
@endsection
