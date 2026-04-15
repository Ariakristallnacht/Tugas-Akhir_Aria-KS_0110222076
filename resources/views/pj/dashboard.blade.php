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
                        <strong>2</strong>
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
                        <span>Kegiatan</span>
                        <span>Petugas</span>
                        <span>Waktu</span>
                        <span>Status</span>
                    </div>
                    <div class="pkm-table__row">
                        <div data-label="Kegiatan"><strong>Imunisasi Keliling</strong><small>Posyandu Melati</small></div>
                        <div data-label="Petugas">Bidan Siska, 1 admin</div>
                        <div data-label="Waktu">09.00 - 11.30</div>
                        <div data-label="Status"><span class="pkm-pill is-blue">Berjalan</span></div>
                    </div>
                    <div class="pkm-table__row">
                        <div data-label="Kegiatan"><strong>Penyuluhan Gizi</strong><small>RW 03</small></div>
                        <div data-label="Petugas">Ahli Gizi, 1 promkes</div>
                        <div data-label="Waktu">13.00 - 15.00</div>
                        <div data-label="Status"><span class="pkm-pill is-amber">Menunggu</span></div>
                    </div>
                </div>
            </section>
        </div>

        <aside class="pkm-dashboard-side">
            <section class="pkm-side-summary">
                <div class="pkm-side-summary__head">
                    <h3>Aksi Cepat</h3>
                </div>
                <a href="#" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="calendar-plus" class="size-4"></i></span>
                    <span><strong>Buat Jadwal</strong><small>Susun jadwal layanan baru.</small></span>
                </a>
                <a href="#" class="pkm-quick-action">
                    <span class="pkm-quick-action__icon"><i data-lucide="shield-check" class="size-4"></i></span>
                    <span><strong>Verifikasi Dinas</strong><small>Setujui atau tolak pengajuan.</small></span>
                </a>
            </section>
        </aside>
    </section>
@endsection
