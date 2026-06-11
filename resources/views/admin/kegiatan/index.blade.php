@extends('layouts.dashboard')

@php
    $title = 'Kelola Layanan | Puskesmas Bunar';
    $heading = 'Kelola Layanan';
@endphp

@push('styles')
    <style>
        .pkm-dashboard-main--kegiatan-directory {
            display: flex;
            flex-direction: column;
            gap: 24px;
            min-width: 0;
        }

        @media (min-width: 1280px) {
            .pkm-management-summary--single-row {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (max-width: 1023px) {
            .pkm-dashboard-main--kegiatan-directory {
                gap: 20px;
            }

            .pkm-table--laporan .pkm-table__head,
            .pkm-table--laporan .pkm-table__row {
                grid-template-columns: 1fr;
            }

            .pkm-table--laporan .pkm-table__head {
                display: none;
            }

            .pkm-table--laporan {
                gap: 12px;
                border: 0;
                background: transparent;
            }

            .pkm-table--laporan .pkm-table__row {
                gap: 10px;
                padding: 16px;
                border: 1px solid var(--pkm-border);
                border-radius: 18px;
                box-shadow: 0 10px 28px rgba(58, 78, 113, 0.06);
            }

            .pkm-table--laporan .pkm-table__row > div {
                display: flex;
                flex-direction: column;
                gap: 4px;
                min-width: 0;
            }

            .pkm-table--laporan .pkm-table__row > div::before {
                content: attr(data-label);
                font-size: 0.74rem;
                font-weight: 700;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                color: #8b9ab0;
            }

            .pkm-table--laporan .pkm-table__row > [data-label="Status"],
            .pkm-table--laporan .pkm-table__row > [data-label="Aksi"] {
                align-items: flex-start;
            }

            .pkm-table--laporan .pkm-table__row > [data-label="Aksi"] .pkm-row-actions {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .pkm-table--laporan .pkm-table__row > [data-label="Aksi"] .pkm-row-actions > * {
                flex: 1 1 220px;
            }
        }

        @media (max-width: 640px) {
            .pkm-dashboard-main--kegiatan-directory {
                gap: 18px;
            }

            .pkm-table--laporan .pkm-table__row {
                padding: 14px;
            }

            .pkm-table--laporan .pkm-table__row strong,
            .pkm-table--laporan .pkm-table__row small {
                overflow-wrap: anywhere;
                word-break: break-word;
            }

            .pkm-table--laporan .pkm-row-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .pkm-table--laporan .pkm-row-actions > * {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <section class="pkm-dashboard-main pkm-dashboard-main--kegiatan-directory">
        <div class="pkm-section-head">
            <div>
                <h2 style="font-weight: bold">Kelola Layanan</h2>
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
                        <span class="pkm-pagination__muted" aria-hidden="true"><i data-lucide="chevron-left" class="size-4"></i></span>
                    @else
                        <a href="{{ $kegiatanList->previousPageUrl() }}" class="pkm-secondary-button" aria-label="Sebelumnya">
                            <i data-lucide="chevron-left" class="size-4"></i>
                        </a>
                    @endif

                    <span class="pkm-pagination__page">{{ $kegiatanList->currentPage() }} / {{ $kegiatanList->lastPage() }}</span>

                    @if ($kegiatanList->hasMorePages())
                        <a href="{{ $kegiatanList->nextPageUrl() }}" class="pkm-secondary-button" aria-label="Berikutnya">
                            <i data-lucide="chevron-right" class="size-4"></i>
                        </a>
                    @else
                        <span class="pkm-pagination__muted" aria-hidden="true"><i data-lucide="chevron-right" class="size-4"></i></span>
                    @endif
                </div>
            @endif
        </section>
    </section>
@endsection
