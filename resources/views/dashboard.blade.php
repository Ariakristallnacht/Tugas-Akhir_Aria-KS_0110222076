@extends('layouts.dashboard')

@php
    $title = 'Dashboard Puskesmas Bunar';
    $heading = 'Overview';
@endphp

@section('content')
    <section class="pkm-dashboard-grid">
        <div class="pkm-dashboard-main">
            <div class="pkm-section-head">
                <div>
                    <h2>General Report</h2>
                    <p>Ringkasan cepat penjadwalan layanan dan kegiatan dinas luar Puskesmas Bunar.</p>
                </div>
                <button class="pkm-refresh-button" type="button">
                    <i data-lucide="refresh-cw" class="size-4"></i>
                    Refresh
                </button>
            </div>

            <div class="pkm-metric-grid">
                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-emerald-100 text-emerald-700">
                        <i data-lucide="users-round" class="size-5"></i>
                    </div>
                    <div class="pkm-metric-card__trend is-up">12%</div>
                    <div class="pkm-metric-card__value">48</div>
                    <div class="pkm-metric-card__label">Pegawai Aktif</div>
                </article>

                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-cyan-100 text-cyan-700">
                        <i data-lucide="calendar-check-2" class="size-5"></i>
                    </div>
                    <div class="pkm-metric-card__trend is-up">9%</div>
                    <div class="pkm-metric-card__value">31</div>
                    <div class="pkm-metric-card__label">Jadwal Tersusun</div>
                </article>

                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-amber-100 text-amber-700">
                        <i data-lucide="briefcase-business" class="size-5"></i>
                    </div>
                    <div class="pkm-metric-card__trend is-down">7%</div>
                    <div class="pkm-metric-card__value">8</div>
                    <div class="pkm-metric-card__label">Pegawai Dinas Luar</div>
                </article>

                <article class="pkm-metric-card">
                    <div class="pkm-metric-card__icon bg-lime-100 text-lime-700">
                        <i data-lucide="file-check-2" class="size-5"></i>
                    </div>
                    <div class="pkm-metric-card__trend is-up">41%</div>
                    <div class="pkm-metric-card__value">18</div>
                    <div class="pkm-metric-card__label">Laporan Masuk</div>
                </article>
            </div>

            <div class="pkm-hero-panel">
                <div class="pkm-hero-panel__content">
                    <span class="pkm-hero-panel__eyebrow">Layanan Kesehatan Terjadwal</span>
                    <h3>Koordinasi jadwal layanan dan dinas luar dalam satu dashboard yang rapi, fokus, dan nyaman dipantau.</h3>
                    <p>Nuansa tampilan sudah dirapikan ke arah dashboard admin yang lebih profesional, dengan struktur panel yang lebih jelas dan ritme visual yang lebih tenang.</p>
                </div>
                <div class="pkm-hero-panel__stats">
                    <div>
                        <span>Layanan Hari Ini</span>
                        <strong>12</strong>
                        <small>5 poli, 7 layanan umum</small>
                    </div>
                    <div>
                        <span>Dinas Luar</span>
                        <strong>4</strong>
                        <small>2 menunggu verifikasi</small>
                    </div>
                </div>
            </div>

            <div class="pkm-chart-row">
                <section class="pkm-card pkm-chart-card pkm-chart-card--wide">
                    <div class="pkm-card__head">
                        <div>
                            <h3>Ringkasan Jadwal Bulanan</h3>
                            <p>Perbandingan beban layanan dan kegiatan lapangan bulan ini.</p>
                        </div>
                        <div class="pkm-card__toolbar">
                            <span>15 Apr 2026 - 15 Mei 2026</span>
                        </div>
                    </div>

                    <div class="pkm-chart-placeholder pkm-chart-placeholder--line">
                        <div class="pkm-chart-placeholder__legend">
                            <span><i></i> Layanan Internal</span>
                            <span><i class="is-secondary"></i> Dinas Luar</span>
                        </div>
                        <div class="pkm-fake-chart">
                            <div class="pkm-fake-chart__grid"></div>
                            <div class="pkm-fake-chart__line pkm-fake-chart__line--primary"></div>
                            <div class="pkm-fake-chart__line pkm-fake-chart__line--secondary"></div>
                        </div>
                    </div>
                </section>

                <section class="pkm-card pkm-chart-card">
                    <div class="pkm-card__head">
                        <div>
                            <h3>Komposisi Kegiatan</h3>
                            <p>Layanan vs dinas luar</p>
                        </div>
                    </div>
                    <div class="pkm-donut-wrap">
                        <div class="pkm-donut-chart"></div>
                        <div class="pkm-donut-chart__label">
                            <strong>73%</strong>
                            <span>Layanan</span>
                        </div>
                    </div>
                </section>
            </div>

            <section class="pkm-card pkm-table-card">
                <div class="pkm-card__head">
                    <div>
                        <h3>Jadwal Layanan Terdekat</h3>
                        <p>Penugasan yang akan berjalan dalam waktu dekat.</p>
                    </div>
                    <a href="#" class="pkm-text-link">Show More</a>
                </div>

                <div class="pkm-table">
                    <div class="pkm-table__head">
                        <span>Kegiatan</span>
                        <span>Petugas</span>
                        <span>Waktu</span>
                        <span>Status</span>
                    </div>
                    <div class="pkm-table__row">
                        <div data-label="Kegiatan">
                            <strong>Pelayanan Poli Umum</strong>
                            <small>Gedung utama lantai 1</small>
                        </div>
                        <div data-label="Petugas">dr. Rina, 2 perawat</div>
                        <div data-label="Waktu">08.00 - 12.00</div>
                        <div data-label="Status"><span class="pkm-pill is-green">Terjadwal</span></div>
                    </div>
                    <div class="pkm-table__row">
                        <div data-label="Kegiatan">
                            <strong>Imunisasi Keliling</strong>
                            <small>Posyandu Melati</small>
                        </div>
                        <div data-label="Petugas">Bidan Siska, 1 admin</div>
                        <div data-label="Waktu">09.00 - 11.30</div>
                        <div data-label="Status"><span class="pkm-pill is-blue">Terjadwal</span></div>
                    </div>
                    <div class="pkm-table__row">
                        <div data-label="Kegiatan">
                            <strong>Penyuluhan Gizi</strong>
                            <small>Balai warga RW 03</small>
                        </div>
                        <div data-label="Petugas">Ahli Gizi, 1 promkes</div>
                        <div data-label="Waktu">13.00 - 15.00</div>
                        <div data-label="Status"><span class="pkm-pill is-amber">Perlu Verifikasi</span></div>
                    </div>
                </div>
            </section>
        </div>

        <aside class="pkm-dashboard-side">
            <section class="pkm-side-panel">
                <div class="pkm-side-panel__head">
                    <h3>Aktivitas Hari Ini</h3>
                    <span>Live</span>
                </div>

                <div class="pkm-activity-list">
                    <article class="pkm-activity-item">
                        <div class="pkm-activity-item__avatar">DR</div>
                        <div class="pkm-activity-item__body">
                            <strong>dr. Rina Permata</strong>
                            <small>Poli umum dimulai pukul 08.00</small>
                        </div>
                        <span class="pkm-activity-item__meta is-positive">+aktif</span>
                    </article>

                    <article class="pkm-activity-item">
                        <div class="pkm-activity-item__avatar">BS</div>
                        <div class="pkm-activity-item__body">
                            <strong>Siska Anggraini</strong>
                            <small>Imunisasi keliling diverifikasi</small>
                        </div>
                        <span class="pkm-activity-item__meta is-neutral">09.15</span>
                    </article>

                    <article class="pkm-activity-item">
                        <div class="pkm-activity-item__avatar">FP</div>
                        <div class="pkm-activity-item__body">
                            <strong>Fauzan Pratama</strong>
                            <small>Penyuluhan gizi menunggu persetujuan</small>
                        </div>
                        <span class="pkm-activity-item__meta is-warning">pending</span>
                    </article>

                    <article class="pkm-activity-item">
                        <div class="pkm-activity-item__avatar">AN</div>
                        <div class="pkm-activity-item__body">
                            <strong>Ani Nurlaila</strong>
                            <small>Laporan kegiatan sudah diunggah</small>
                        </div>
                        <span class="pkm-activity-item__meta is-positive">done</span>
                    </article>
                </div>

                <a href="#" class="pkm-side-panel__button">Lihat Semua Aktivitas</a>
            </section>

            <section class="pkm-side-summary">
                <div class="pkm-side-summary__head">
                    <h3>Agenda Cepat</h3>
                    <a href="#" class="pkm-text-link">Show More</a>
                </div>

                <a href="#" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="calendar-plus" class="size-4"></i></span>
                    <span>
                        <strong>Susun Jadwal Layanan</strong>
                        <small>Atur poli, posyandu, dan kegiatan internal.</small>
                    </span>
                </a>

                <a href="#" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="briefcase-medical" class="size-4"></i></span>
                    <span>
                        <strong>Verifikasi Dinas Luar</strong>
                        <small>Periksa pengajuan kegiatan pegawai lapangan.</small>
                    </span>
                </a>

                <a href="#" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="file-text" class="size-4"></i></span>
                    <span>
                        <strong>Tinjau Laporan</strong>
                        <small>Pantau laporan kegiatan yang sudah masuk.</small>
                    </span>
                </a>
            </section>
        </aside>
    </section>
@endsection
