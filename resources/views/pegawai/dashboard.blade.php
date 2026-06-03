@extends('layouts.dashboard')

@php
    $title = 'Dashboard Pegawai | Puskesmas Bunar';
    $heading = 'Dashboard';
@endphp

@push('styles')
    <style>
        @media (min-width: 1280px) {
            .pkm-management-summary--single-row {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
    </style>
@endpush

@section('content')
    <section class="pkm-dashboard-grid">
        <div class="pkm-dashboard-main">
            <div class="pkm-section-head">
                <div>
                    <h2 style="font-weight: bold">Dashboard</h2>
                </div>
            </div>

            <div class="pkm-management-summary pkm-management-summary--single-row">
                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-cyan-100 text-cyan-700"><i data-lucide="calendar-range" class="size-5"></i></div>
                    <div class="pkm-metric-card__value">{{ $todayScheduleCount }}</div>
                    <div class="pkm-metric-card__label">Jadwal Hari Ini</div>
                </article>
                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-amber-100 text-amber-700"><i data-lucide="clock-3" class="size-5"></i></div>
                    <div class="pkm-metric-card__value">{{ $pendingSubmissionCount }}</div>
                    <div class="pkm-metric-card__label">Menunggu Verifikasi</div>
                </article>
                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-emerald-100 text-emerald-700"><i data-lucide="badge-check" class="size-5"></i></div>
                    <div class="pkm-metric-card__value">{{ $approvedSubmissionCount }}</div>
                    <div class="pkm-metric-card__label">Pengajuan Disetujui</div>
                </article>
                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-lime-100 text-lime-700"><i data-lucide="briefcase-business" class="size-5"></i></div>
                    <div class="pkm-metric-card__value">{{ $submissionCount }}</div>
                    <div class="pkm-metric-card__label">Total Pengajuan</div>
                </article>
            </div>

            <section class="pkm-card pkm-table-card">
                <div class="pkm-card__head">
                    <div>
                        <h3 style="font-weight: bold">Fokus Saya</h3>
                    </div>
                </div>

                <div class="pkm-table">
                    <div class="pkm-table__head">
                        <span>Kegiatan</span>
                        <span>Peran</span>
                        <span>Waktu</span>
                        <span>Status</span>
                    </div>
                    @forelse ($upcomingSchedules as $schedule)
                        <div class="pkm-table__row">
                            <div data-label="Kegiatan">
                                <strong>{{ $schedule->kegiatan?->nama_kegiatan ?? 'Kegiatan' }}</strong>
                                <small>{{ $schedule->lokasi }}</small>
                            </div>
                            <div data-label="Peran">{{ $schedule->pivot?->peran_tugas ?? 'Petugas' }}</div>
                            <div data-label="Waktu">
                                {{ $schedule->tanggal?->translatedFormat('d M Y') }}
                                <small>{{ ($schedule->waktu_mulai?->format('H:i') ?? '--:--') .' - '. ($schedule->waktu_selesai?->format('H:i') ?? '--:--') }}</small>
                            </div>
                            <div data-label="Status"><span class="pkm-pill is-green">{{ ucfirst($schedule->pivot?->status_penugasan ?? 'terjadwal') }}</span></div>
                        </div>
                    @empty
                        <div class="pkm-table__row">
                            <div data-label="Kegiatan"><strong>Belum ada jadwal terdekat</strong><small>Jadwal layanan akan tampil saat sudah ditetapkan.</small></div>
                            <div data-label="Peran">-</div>
                            <div data-label="Waktu">-</div>
                            <div data-label="Status"><span class="pkm-pill is-amber">Kosong</span></div>
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
                <a href="{{ route('pegawai.pengajuan-dinas.index') }}" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="briefcase-business" class="size-4"></i></span>
                    <span><strong>Pengajuan Dinas</strong><small>Lihat dan kelola pengajuan dinas luar Anda.</small></span>
                </a>
                <a href="{{ route('pegawai.laporan-kegiatan.create') }}" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="file-plus-2" class="size-4"></i></span>
                    <span><strong>Tambah Laporan</strong><small>Kirim laporan kegiatan sesuai jadwal tugas Anda.</small></span>
                </a>
                <a href="{{ route('pegawai.pengajuan-dinas.create') }}" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="file-plus-2" class="size-4"></i></span>
                    <span><strong>Buat Pengajuan</strong><small>Ajukan kegiatan lapangan baru dengan lebih cepat.</small></span>
                </a>
                <a href="{{ route('pegawai.jadwal-kegiatan') }}" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="calendar-range" class="size-4"></i></span>
                    <span><strong>Lihat Jadwal</strong><small>Cek tugas layanan dan agenda dinas terdekat.</small></span>
                </a>
                <div class="pkm-side-summary__footnote">
                    Agenda terdekat tercatat: <strong>{{ $upcomingSchedules->count() }}</strong>
                </div>
            </section>
        </aside>
    </section>
@endsection
