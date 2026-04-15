@extends('layouts.dashboard')

@php
    $title = 'Dashboard Pegawai | Puskesmas Bunar';
    $heading = 'Dashboard Pegawai';
@endphp

@section('content')
    <section class="pkm-dashboard-grid">
        <div class="pkm-dashboard-main">
            <div class="pkm-section-head">
                <div>
                    <h2>Informasi Tugas</h2>
                    <p>Lihat jadwal layanan, status dinas luar, dan ringkasan kegiatan pribadi.</p>
                </div>
            </div>

            <div class="pkm-hero-panel">
                <div class="pkm-hero-panel__content">
                    <span class="pkm-hero-panel__eyebrow">Akses Pegawai</span>
                    <h3>Akses cepat untuk melihat jadwal dinas, mengajukan kegiatan luar, dan memantau tugas harian.</h3>
                    <p>Semua informasi penting pegawai disederhanakan agar mudah dipakai dari desktop maupun mobile.</p>
                </div>
                <div class="pkm-hero-panel__stats">
                    <div>
                        <span>Jadwal Saya</span>
                        <strong>3</strong>
                        <small>Tugas aktif minggu ini</small>
                    </div>
                    <div>
                        <span>Pengajuan</span>
                        <strong>1</strong>
                        <small>Masih diproses</small>
                    </div>
                </div>
            </div>

            <section class="pkm-card pkm-table-card">
                <div class="pkm-card__head">
                    <div>
                        <h3>Jadwal Saya</h3>
                        <p>Penugasan terdekat yang perlu diperhatikan.</p>
                    </div>
                </div>

                <div class="pkm-table">
                    <div class="pkm-table__head">
                        <span>Kegiatan</span>
                        <span>Peran</span>
                        <span>Waktu</span>
                        <span>Status</span>
                    </div>
                    <div class="pkm-table__row">
                        <div data-label="Kegiatan"><strong>Pelayanan Poli Umum</strong><small>Gedung utama lantai 1</small></div>
                        <div data-label="Peran">Perawat Pendamping</div>
                        <div data-label="Waktu">08.00 - 12.00</div>
                        <div data-label="Status"><span class="pkm-pill is-green">Terjadwal</span></div>
                    </div>
                </div>
            </section>
        </div>

        <aside class="pkm-dashboard-side">
            <section class="pkm-side-summary">
                <div class="pkm-side-summary__head">
                    <h3>Aksi Saya</h3>
                </div>
                <a href="#" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="briefcase-business" class="size-4"></i></span>
                    <span><strong>Ajukan Dinas Luar</strong><small>Buat pengajuan kegiatan lapangan.</small></span>
                </a>
                <a href="#" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="calendar-range" class="size-4"></i></span>
                    <span><strong>Lihat Jadwal</strong><small>Cek tugas layanan dan dinas luar.</small></span>
                </a>
            </section>
        </aside>
    </section>
@endsection
