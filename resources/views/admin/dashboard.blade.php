@extends('layouts.dashboard')

@php
    $title = 'Dashboard Admin | Puskesmas Bunar';
    $heading = 'Dashboard Admin';
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
                    <h2 style="font-weight: bold">Dashboard Admin</h2>
                </div>
            </div>

            <div class="pkm-management-summary pkm-management-summary--single-row">
                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-emerald-100 text-emerald-700"><i data-lucide="users-round" class="size-5"></i></div>
                    <div class="pkm-metric-card__value">{{ $totalPegawai }}</div>
                    <div class="pkm-metric-card__label">Pegawai Aktif</div>
                </article>
                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-cyan-100 text-cyan-700"><i data-lucide="calendar-check-2" class="size-5"></i></div>
                    <div class="pkm-metric-card__value">{{ $jadwalHariIni }}</div>
                    <div class="pkm-metric-card__label">Jadwal Hari Ini</div>
                </article>
                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-amber-100 text-amber-700"><i data-lucide="shield-check" class="size-5"></i></div>
                    <div class="pkm-metric-card__value">{{ $laporanMenunggu }}</div>
                    <div class="pkm-metric-card__label">Menunggu Verifikasi</div>
                </article>
                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-lime-100 text-lime-700"><i data-lucide="file-text" class="size-5"></i></div>
                    <div class="pkm-metric-card__value">{{ $totalLaporan }}</div>
                    <div class="pkm-metric-card__label">Total Laporan</div>
                </article>
            </div>

            <section class="pkm-card pkm-table-card">
                <div class="pkm-card__head">
                    <div>
                        <h3 style="font-weight: bold">Fokus Admin</h3>
                    </div>
                </div>

                <div class="pkm-table">
                    <div class="pkm-table__head">
                        <span>Laporan</span>
                        <span>Pegawai</span>
                        <span>Tanggal</span>
                        <span>Status</span>
                    </div>
                    @forelse ($recentReports as $report)
                        @php
                            $statusClass = match ($report->status_verifikasi) {
                                'disetujui' => 'is-green',
                                'ditolak' => 'is-amber',
                                default => 'is-blue',
                            };
                        @endphp
                        <div class="pkm-table__row">
                            <div data-label="Laporan">
                                <strong>{{ $report->jadwal?->kegiatan?->nama_kegiatan ?? 'Kegiatan tidak ditemukan' }}</strong>
                                <small>{{ $report->jadwal?->lokasi ?? 'Lokasi belum diisi' }}</small>
                            </div>
                            <div data-label="Pegawai">
                                <strong>{{ $report->pegawai?->nama ?? 'Pegawai tidak ditemukan' }}</strong>
                                <small>{{ $report->pegawai?->jabatan ?? 'Jabatan tidak tersedia' }}</small>
                            </div>
                            <div data-label="Tanggal">
                                {{ $report->tanggal?->translatedFormat('d M Y') ?? '-' }}
                                <small>{{ $report->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</small>
                            </div>
                            <div data-label="Status">
                                <span class="pkm-pill {{ $statusClass }}">{{ ucfirst($report->status_verifikasi ?? 'menunggu') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="pkm-table__row">
                            <div data-label="Laporan"><strong>Belum ada laporan terbaru</strong><small>Data laporan akan muncul di sini.</small></div>
                            <div data-label="Pegawai">-</div>
                            <div data-label="Tanggal">-</div>
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
                <a href="{{ route('admin.monitoring-jadwal') }}" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="calendar-range" class="size-4"></i></span>
                    <span><strong>Monitoring Jadwal</strong><small>{{ $jadwalHariIni }} agenda tercatat untuk hari ini.</small></span>
                </a>
                <a href="{{ route('admin.pegawai.index') }}" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="briefcase-business" class="size-4"></i></span>
                    <span><strong>Kelola Pegawai</strong><small>Perbarui data pegawai dan akun yang aktif.</small></span>
                </a>
                <a href="{{ route('admin.monitoring-laporan') }}" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="file-spreadsheet" class="size-4"></i></span>
                    <span><strong>Monitoring Laporan</strong><small>{{ $laporanMenunggu }} laporan masih menunggu verifikasi.</small></span>
                </a>
                <a href="{{ route('admin.pegawai.create') }}" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="user-plus" class="size-4"></i></span>
                    <span><strong>Tambah Pegawai</strong><small>Buat data pegawai baru beserta akun bila diperlukan.</small></span>
                </a>
                <div class="pkm-side-summary__footnote">
                    Total akun terdaftar: <strong>{{ $totalAccounts }}</strong>
                </div>
            </section>
        </aside>
    </section>
@endsection
