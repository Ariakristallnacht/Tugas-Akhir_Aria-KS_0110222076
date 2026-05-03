@extends('layouts.dashboard')

@php
    $title = 'Monitoring Laporan Kegiatan | Puskesmas Bunar';
    $heading = 'Monitoring Laporan Kegiatan';
@endphp

@push('styles')
    <style>
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

        .pkm-report-search-inline label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            color: #60748f;
        }

        .pkm-table--monitoring-laporan .pkm-table__head,
        .pkm-table--monitoring-laporan .pkm-table__row {
            grid-template-columns: minmax(0, 1.55fr) minmax(180px, 0.9fr) minmax(180px, 0.82fr) minmax(180px, 0.85fr) minmax(140px, 0.68fr);
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
                <h2 style="font-weight: bold">Monitoring Laporan Kegiatan</h2>
            </div>
        </div>

        <div class="pkm-management-summary">
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-cyan-100 text-cyan-700"><i data-lucide="file-text" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['all'] }}</div>
                <div class="pkm-metric-card__label">Total Laporan</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-amber-100 text-amber-700"><i data-lucide="users-round" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['pegawai'] }}</div>
                <div class="pkm-metric-card__label">Pegawai Pelaksana</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-emerald-100 text-emerald-700"><i data-lucide="folder-kanban" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['kegiatan'] }}</div>
                <div class="pkm-metric-card__label">Jenis Kegiatan</div>
            </article>
        </div>

        <section class="pkm-card">
            <div class="pkm-card__head">
                <div>
                    <h3 style="font-weight: bold">Filter dan Export</h3>
                    <br>
                </div>
                </div>

            <form method="GET" action="{{ route('admin.monitoring-laporan') }}" class="pkm-monitoring-filter">
                <div class="pkm-form-grid">
                    <div class="pkm-field">
                        <label for="date_from">Tanggal awal</label>
                        <input id="date_from" class="pkm-input" type="date" name="date_from" value="{{ $filters['date_from'] }}">
                    </div>
                    <div class="pkm-field">
                        <label for="date_to">Tanggal akhir</label>
                        <input id="date_to" class="pkm-input" type="date" name="date_to" value="{{ $filters['date_to'] }}">
                    </div>
                    <div class="pkm-field">
                        <label for="pegawai_id">Pegawai</label>
                        <select id="pegawai_id" class="pkm-input" name="pegawai_id">
                            <option value="">Semua Pegawai</option>
                            @foreach ($pegawaiOptions as $pegawai)
                                <option value="{{ $pegawai->id }}" @selected((string) $filters['pegawai_id'] === (string) $pegawai->id)>{{ $pegawai->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pkm-form-actions">
                    <a href="{{ route('admin.monitoring-laporan') }}" class="pkm-secondary-button"><i data-lucide="rotate-ccw" class="size-4"></i><span>Reset</span></a>
                    <button type="submit" class="pkm-primary-button"><i data-lucide="funnel" class="size-4"></i><span>Terapkan Filter</span></button>
                </div>
            </form>
        </section>

        <section class="pkm-card pkm-table-card">
            <div class="pkm-card__head">
                <div class="pkm-report-table-head">
                    <div>
                        <h3 style="font-weight: bold">Daftar Laporan</h3>
                    </div>
                    <form method="GET" action="{{ route('admin.monitoring-laporan') }}" class="pkm-report-search-inline">
                        <input type="hidden" name="date_from" value="{{ $filters['date_from'] }}">
                        <input type="hidden" name="date_to" value="{{ $filters['date_to'] }}">
                        <input type="hidden" name="pegawai_id" value="{{ $filters['pegawai_id'] }}">
                        <input id="table-search" class="pkm-input" type="text" name="search" value="{{ $filters['search'] }}" placeholder="Cari nama pegawai, kegiatan, lokasi, atau isi laporan">
                    </form>
                </div>
            </div>

            @if ($reports->isEmpty())
                <div class="pkm-empty-state">
                    <strong>Belum ada laporan kegiatan pada filter ini.</strong>
                </div>
            @else
                <div class="pkm-table pkm-table--laporan pkm-table--monitoring-laporan">
                    <div class="pkm-table__head">
                        <span>Laporan</span>
                        <span>Pelaksana</span>
                        <span>Jadwal</span>
                        <span>Waktu Dibuat</span>
                        <span>Lihat Laporan</span>
                    </div>

                    @foreach ($reports as $report)
                        <div class="pkm-table__row">
                            <div data-label="Laporan">
                                <strong>{{ $report->jadwal?->kegiatan?->nama_kegiatan ?? 'Kegiatan tidak ditemukan' }}</strong>
                                <small>{{ $report->tanggal->translatedFormat('d F Y') }} · {{ $report->jadwal?->lokasi ?? 'Lokasi belum diisi' }}</small>
                                <p class="pkm-report-snippet">{{ \Illuminate\Support\Str::limit($report->laporan, 180) }}</p>
                            </div>
                            <div data-label="Pelaksana">
                                <strong>{{ $report->pegawai?->nama ?? 'Pegawai tidak ditemukan' }}</strong>
                                <small>{{ $report->pegawai?->jabatan ?? 'Jabatan tidak tersedia' }}</small>
                            </div>
                            <div data-label="Jadwal">
                                <strong>{{ $report->jadwal?->waktu_mulai?->format('H:i') ?? '-' }} - {{ $report->jadwal?->waktu_selesai?->format('H:i') ?? '-' }}</strong>
                                <small>Status jadwal: {{ ucfirst($report->jadwal?->status ?? 'tidak diketahui') }}</small>
                            </div>
                            <div data-label="Waktu Dibuat">
                                <strong>{{ $report->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</strong>
                                <small>Terakhir diperbarui: {{ $report->updated_at?->translatedFormat('d M Y H:i') ?? '-' }}</small>
                            </div>
                            <div data-label="Lihat Laporan">
                                @if ($report->dokumen_laporan_url)
                                    <a href="{{ $report->dokumen_laporan_url }}" target="_blank" rel="noopener noreferrer" class="pkm-text-link">
                                        <i data-lucide="file-text" class="size-4"></i>
                                        <span>Lihat PDF</span>
                                    </a>
                                    <small>{{ $report->dokumen_laporan_nama ?? 'Dokumen laporan' }}</small>
                                @else
                                    <strong>Belum diunggah</strong>
                                    <small>PJ belum mengunggah file PDF.</small>
                                @endif
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
