@extends('layouts.dashboard')

@php
    $title = 'Dashboard Admin | Puskesmas Bunar';
    $heading = 'Dashboard Admin';
@endphp

@section('content')
    <section class="pkm-dashboard-grid">
        <div class="pkm-dashboard-main">
            <div class="pkm-section-head">
                <div>
                    <h2>Ringkasan Admin</h2>
                    <p>Kelola akun, pegawai, monitoring kegiatan, dan laporan dalam satu panel.</p>
                </div>
            </div>

            <div class="pkm-metric-grid">
                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-emerald-100 text-emerald-700"><i data-lucide="users-round" class="size-5"></i></div>
                    <div class="pkm-metric-card__trend is-up">12%</div>
                    <div class="pkm-metric-card__value">{{ $totalPegawai }}</div>
                    <div class="pkm-metric-card__label">Pegawai Aktif</div>
                </article>
                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-cyan-100 text-cyan-700"><i data-lucide="calendar-check-2" class="size-5"></i></div>
                    <div class="pkm-metric-card__trend is-up">9%</div>
                    <div class="pkm-metric-card__value">31</div>
                    <div class="pkm-metric-card__label">Jadwal Tersusun</div>
                </article>
                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-amber-100 text-amber-700"><i data-lucide="shield-check" class="size-5"></i></div>
                    <div class="pkm-metric-card__trend is-down">3%</div>
                    <div class="pkm-metric-card__value">{{ $laporanMenunggu }}</div>
                    <div class="pkm-metric-card__label">Butuh Verifikasi</div>
                </article>
                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-lime-100 text-lime-700"><i data-lucide="file-text" class="size-5"></i></div>
                    <div class="pkm-metric-card__trend is-up">18%</div>
                    <div class="pkm-metric-card__value">{{ $totalLaporan }}</div>
                    <div class="pkm-metric-card__label">Laporan Masuk</div>
                </article>
            </div>

            <div class="pkm-hero-panel">
                <div class="pkm-hero-panel__content">
                    <span class="pkm-hero-panel__eyebrow">Akses Admin</span>
                    <h3>Pantau keseluruhan operasional Puskesmas Bunar dari akun administrator.</h3>
                    <p>Admin dapat mengelola akun, data pegawai, monitoring layanan, dan laporan kegiatan tanpa berpindah alur kerja.</p>
                </div>
                <div class="pkm-hero-panel__stats">
                    <div>
                        <span>Akun Aktif</span>
                        <strong>{{ $totalAccounts }}</strong>
                        <small>Termasuk admin, PJ, dan pegawai</small>
                    </div>
                    <div>
                        <span>Monitoring</span>
                        <strong>12</strong>
                        <small>Kegiatan aktif hari ini</small>
                    </div>
                </div>
            </div>
        </div>

        <aside class="pkm-dashboard-side">
            <section class="pkm-side-panel">
                <div class="pkm-side-panel__head">
                    <h3>Menu Prioritas</h3>
                    <span>Admin</span>
                </div>
                <div class="pkm-activity-list">
                    <a href="{{ route('admin.monitoring-jadwal') }}" class="pkm-activity-item">
                        <div class="pkm-activity-item__avatar">MJ</div>
                        <div class="pkm-activity-item__body">
                            <strong>Jadwal Kegiatan</strong>
                            <small>Lihat jadwal layanan dan dinas luar dalam tampilan list dan kalender.</small>
                        </div>
                    </a>
                    <a href="{{ route('admin.pegawai.index') }}" class="pkm-activity-item">
                        <div class="pkm-activity-item__avatar">PG</div>
                        <div class="pkm-activity-item__body">
                            <strong>Kelola Pegawai</strong>
                            <small>Atur data pegawai sekaligus akun login admin, PJ, dan pegawai.</small>
                        </div>
                    </a>
                    <a href="{{ route('admin.monitoring-laporan') }}" class="pkm-activity-item">
                        <div class="pkm-activity-item__avatar">LK</div>
                        <div class="pkm-activity-item__body">
                            <strong>Monitoring Laporan</strong>
                            <small>Pantau laporan kegiatan dan unduh dokumen PDF, Excel, atau CSV.</small>
                        </div>
                    </a>
                    <a href="{{ route('admin.pegawai.create') }}" class="pkm-activity-item">
                        <div class="pkm-activity-item__avatar">TP</div>
                        <div class="pkm-activity-item__body">
                            <strong>Tambah Pegawai</strong>
                            <small>Buat data pegawai baru dan aktifkan akun login bila diperlukan.</small>
                        </div>
                    </a>
                </div>
            </section>
        </aside>
    </section>
@endsection
