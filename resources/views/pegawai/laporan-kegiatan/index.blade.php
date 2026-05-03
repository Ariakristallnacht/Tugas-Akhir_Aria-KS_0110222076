@extends('layouts.dashboard')

@php
    $title = 'Laporan Saya | Puskesmas Bunar';
    $heading = 'Laporan Saya';
@endphp

@push('styles')
    <style>
        @media (min-width: 1280px) {
            .pkm-management-summary--single-row {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .pkm-report-table-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            width: 100%;
        }

        .pkm-report-search-inline {
            width: min(420px, 100%);
            margin-left: auto;
        }

        @media (max-width: 1023px) {
            .pkm-report-table-head {
                align-items: stretch;
                flex-direction: column;
            }

            .pkm-report-search-inline {
                width: 100%;
                margin-left: 0;
            }
        }
    </style>
@endpush

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-section-head">
            <div>
                <h2 style="font-weight: bold">Laporan Saya</h2>
            </div>
            <a href="{{ route('pegawai.laporan-kegiatan.create') }}" class="pkm-primary-button">
                <i data-lucide="plus" class="size-4"></i>
                <span>Tambah Laporan</span>
            </a>
        </div>

        @include('admin.partials.flash')

        <div class="pkm-management-summary pkm-management-summary--single-row">
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-cyan-100 text-cyan-700"><i data-lucide="files" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['all'] }}</div>
                <div class="pkm-metric-card__label">Total Laporan</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-emerald-100 text-emerald-700"><i data-lucide="folder-kanban" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['kegiatan'] }}</div>
                <div class="pkm-metric-card__label">Jenis Kegiatan</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-rose-100 text-rose-700"><i data-lucide="calendar-days" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['bulan_ini'] }}</div>
                <div class="pkm-metric-card__label">Laporan Bulan Ini</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-amber-100 text-amber-700"><i data-lucide="file-check-2" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['dokumen'] }}</div>
                <div class="pkm-metric-card__label">Dokumen Terunggah</div>
            </article>
        </div>

        <section class="pkm-card">
            <div class="pkm-card__head">
                <div>
                    <h3 style="font-weight: bold">Filter Laporan</h3>
                    <br>
                </div>
            </div>

            <form method="GET" action="{{ route('pegawai.laporan-kegiatan.index') }}" class="pkm-monitoring-filter">
                <div class="pkm-form-grid">
                    <div class="pkm-field">
                        <label for="month">Bulan laporan</label>
                        <input id="month" class="pkm-input" type="month" name="month" value="{{ $filters['month'] }}">
                    </div>
                    <div class="pkm-field">
                        <label for="date_from">Tanggal awal</label>
                        <input id="date_from" class="pkm-input" type="date" name="date_from" value="{{ $filters['date_from'] }}">
                    </div>
                    <div class="pkm-field">
                        <label for="date_to">Tanggal akhir</label>
                        <input id="date_to" class="pkm-input" type="date" name="date_to" value="{{ $filters['date_to'] }}">
                    </div>
                </div>

                <div class="pkm-form-actions">
                    <a href="{{ route('pegawai.laporan-kegiatan.index') }}" class="pkm-secondary-button"><i data-lucide="rotate-ccw" class="size-4"></i><span>Reset</span></a>
                    <button type="submit" class="pkm-primary-button"><i data-lucide="funnel" class="size-4"></i><span>Terapkan Filter</span></button>
                </div>
            </form>
        </section>

        <section class="pkm-card pkm-table-card">
            <div class="pkm-card__head">
                <div class="pkm-report-table-head">
                    <div>
                        <h3 style="font-weight: bold">Daftar Laporan Saya</h3>
                    </div>
                    <form method="GET" action="{{ route('pegawai.laporan-kegiatan.index') }}" class="pkm-report-search-inline">
                        <input type="hidden" name="month" value="{{ $filters['month'] }}">
                        <input type="hidden" name="date_from" value="{{ $filters['date_from'] }}">
                        <input type="hidden" name="date_to" value="{{ $filters['date_to'] }}">
                        <input id="table-search" class="pkm-input" type="text" name="search" value="{{ $filters['search'] }}" placeholder="Cari kegiatan atau lokasi" autocomplete="off">
                    </form> 
                </div>
            </div>
            <br>

            @if ($reports->isEmpty())
                <div class="pkm-empty-state">
                    <strong>Belum ada laporan kegiatan.</strong>
                </div>
            @else
                <div class="pkm-table pkm-table--laporan">
                    <div class="pkm-table__head">
                        <span>Laporan</span>
                        <span>Jadwal</span>
                        <span>Waktu Dibuat</span>
                        <span>Aksi</span>
                    </div>

                    @foreach ($reports as $report)
                        <div class="pkm-table__row">
                            <div data-label="Laporan">
                                <strong>{{ $report->jadwal?->kegiatan?->nama_kegiatan ?? 'Kegiatan tidak ditemukan' }}</strong>
                                <small>{{ $report->tanggal?->translatedFormat('d F Y') ?? '-' }} - {{ $report->jadwal?->lokasi ?? 'Lokasi belum diisi' }}</small>
                            </div>
                            <div data-label="Jadwal">
                                <strong>{{ $report->jadwal?->waktu_mulai?->format('H:i') ?? '-' }} - {{ $report->jadwal?->waktu_selesai?->format('H:i') ?? '-' }}</strong>
                                <small>Status jadwal: {{ ucfirst($report->jadwal?->status ?? 'tidak diketahui') }}</small>
                            </div>
                            <div data-label="Waktu Dibuat">
                                <strong>{{ $report->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</strong>
                                <small>Terakhir diperbarui: {{ $report->updated_at?->translatedFormat('d M Y H:i') ?? '-' }}</small>
                            </div>
                            <div data-label="Aksi">
                                <div class="pkm-row-actions">
                                    <a href="{{ route('pegawai.laporan-kegiatan.show', $report) }}" class="pkm-text-link">
                                        <i data-lucide="eye" class="size-4"></i>
                                        <span>Lihat</span>
                                    </a>
                                    <a href="{{ route('pegawai.laporan-kegiatan.edit', $report) }}" class="pkm-text-link">
                                        <i data-lucide="pencil" class="size-4"></i>
                                        <span>Edit</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pkm-pagination">
                    @if ($reports->onFirstPage())
                        <span class="pkm-pagination__muted">Sebelumnya</span>
                    @else
                        <a href="{{ $reports->previousPageUrl() }}" class="pkm-secondary-button"><i data-lucide="chevron-left" class="size-4"></i><span>Sebelumnya</span></a>
                    @endif

                    <span>Halaman {{ $reports->currentPage() }} dari {{ $reports->lastPage() }}</span>

                    @if ($reports->hasMorePages())
                        <a href="{{ $reports->nextPageUrl() }}" class="pkm-secondary-button"><span>Berikutnya</span><i data-lucide="chevron-right" class="size-4"></i></a>
                    @else
                        <span class="pkm-pagination__muted">Berikutnya</span>
                    @endif
                </div>
            @endif
        </section>
    </section>
@endsection
