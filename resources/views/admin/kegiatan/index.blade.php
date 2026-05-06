@extends('layouts.dashboard')

@php
    $title = 'Data Layanan | Puskesmas Bunar';
    $heading = 'Data Layanan';
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
    <section class="pkm-dashboard-main">
        <div class="pkm-section-head">
            <div>
                <h2 style="font-weight: bold">Kelola Layanan Poli</h2>
            </div>
            <a href="{{ route('pj.kegiatan.create') }}" class="pkm-primary-button">
                <i data-lucide="plus" class="size-4"></i>
                <span>Tambah Layanan</span>
            </a>
        </div>

        @include('admin.partials.flash')

        <div class="pkm-management-summary pkm-management-summary--single-row">
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-cyan-100 text-cyan-700"><i data-lucide="folders" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['total'] }}</div>
                <div class="pkm-metric-card__label">Total Layanan</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-emerald-100 text-emerald-700"><i data-lucide="stethoscope" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['layanan'] }}</div>
                <div class="pkm-metric-card__label">Layanan</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-amber-100 text-amber-700"><i data-lucide="calendar-range" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['jadwal'] }}</div>
                <div class="pkm-metric-card__label">Jadwal Terkait</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-lime-100 text-lime-700"><i data-lucide="badge-check" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['aktif'] }}</div>
                <div class="pkm-metric-card__label">Layanan Aktif</div>
            </article>
        </div>

        <section class="pkm-card">
            <div class="pkm-card__head">
                <div>
                    <h3 style="font-weight: bold">Filter Layanan</h3>
                    <br>
                </div>
            </div>

            <form method="GET" action="{{ route('pj.kegiatan.index') }}" class="pkm-monitoring-filter">
                <div class="pkm-form-grid">
                    <div class="pkm-field pkm-field--full">
                        <label for="search">Cari layanan</label>
                        <input id="search" class="pkm-input" type="text" name="search" value="{{ $filters['search'] }}" placeholder="Cari nama layanan atau deskripsi">
                    </div>
                </div>

                <div class="pkm-form-actions">
                    <a href="{{ route('pj.kegiatan.index') }}" class="pkm-secondary-button">
                        
                        <span>Reset</span>
                    </a>
                    <button type="submit" class="pkm-primary-button">
                        
                        <span>Filter</span>
                    </button>
                </div>
            </form>
        </section>

        <section class="pkm-card pkm-table-card">
            <div class="pkm-card__head">
                <div>
                    <h3 style="font-weight: bold">Daftar Kegiatan</h3>
                </div>
            </div>

                    @if ($kegiatanList->isEmpty())
                <div class="pkm-empty-state">
                    <strong>Belum ada data layanan.</strong>
                    <p>Tambahkan layanan baru agar pilihan pada penjadwalan bisa dikelola dari database.</p>
                </div>
            @else
                <div class="pkm-table pkm-table--laporan">
                    <div class="pkm-table__head">
                        <span>Layanan</span>
                        <span>Info</span>
                        <span>Status</span>
                        <span>Aksi</span>
                    </div>

                    @foreach ($kegiatanList as $kegiatan)
                        <div class="pkm-table__row">
                            <div data-label="Layanan">
                                <strong>{{ $kegiatan->nama_kegiatan }}</strong>
                                <small>{{ $kegiatan->deskripsi ?: 'Deskripsi belum diisi.' }}</small>
                            </div>
                            <div data-label="Info">
                                <strong>{{ $kegiatan->jadwal()->count() }} jadwal terkait</strong>
                                <small>Dibuat {{ $kegiatan->created_at?->translatedFormat('d M Y') ?? '-' }}</small>
                            </div>
                            <div data-label="Status">
                                <span class="pkm-pill {{ $kegiatan->is_aktif ? 'is-green' : 'is-amber' }}">
                                    {{ $kegiatan->is_aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <div data-label="Aksi">
                                <div class="pkm-row-actions">
                                    <a href="{{ route('pj.kegiatan.edit', $kegiatan) }}" class="pkm-text-link">
                                        <i data-lucide="pencil" class="size-4"></i>
                                        <span>Edit</span>
                                    </a>
                                    <form method="POST" action="{{ route('pj.kegiatan.destroy', $kegiatan) }}" onsubmit="return confirm('Hapus layanan ini beserta semua jadwal, laporan, monitoring, dan penugasan terkait?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="pkm-danger-button">
                                            <i data-lucide="trash-2" class="size-4"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pkm-pagination">
                    @if ($kegiatanList->onFirstPage())
                        <span class="pkm-pagination__muted">Sebelumnya</span>
                    @else
                        <a href="{{ $kegiatanList->previousPageUrl() }}" class="pkm-secondary-button">
                            <i data-lucide="chevron-left" class="size-4"></i>
                            <span>Sebelumnya</span>
                        </a>
                    @endif

                    <span>Halaman {{ $kegiatanList->currentPage() }} dari {{ $kegiatanList->lastPage() }}</span>

                    @if ($kegiatanList->hasMorePages())
                        <a href="{{ $kegiatanList->nextPageUrl() }}" class="pkm-secondary-button">
                            <span>Berikutnya</span>
                            <i data-lucide="chevron-right" class="size-4"></i>
                        </a>
                    @else
                        <span class="pkm-pagination__muted">Berikutnya</span>
                    @endif
                </div>
            @endif
        </section>
    </section>
@endsection
