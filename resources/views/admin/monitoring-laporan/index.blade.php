@extends('layouts.dashboard')

@php
    $title = 'Monitoring Laporan Kegiatan | Puskesmas Bunar';
    $heading = 'Monitoring Laporan Kegiatan';
@endphp

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
                    <div class="pkm-field">
                        <label for="pegawai_id">Pegawai</label>
                        <select id="pegawai_id" class="pkm-input" name="pegawai_id">
                            <option value="">Semua Pegawai</option>
                            @foreach ($pegawaiOptions as $pegawai)
                                <option value="{{ $pegawai->id }}" @selected((string) $filters['pegawai_id'] === (string) $pegawai->id)>{{ $pegawai->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pkm-field pkm-field--full">
                        <label for="search">Cari laporan</label>
                        <input id="search" class="pkm-input" type="text" name="search" value="{{ $filters['search'] }}" placeholder="Cari nama pegawai, kegiatan, lokasi, atau isi laporan">
                    </div>
                </div>

                <div class="pkm-form-actions">
                    <div class="pkm-export-actions">
                        <a href="{{ route('admin.monitoring-laporan.export', ['format' => 'pdf'] + request()->query()) }}" class="pkm-secondary-button"><i data-lucide="download" class="size-4"></i><span>Unduh PDF</span></a>
                        <a href="{{ route('admin.monitoring-laporan.export', ['format' => 'xls'] + request()->query()) }}" class="pkm-secondary-button"><i data-lucide="download" class="size-4"></i><span>Unduh Excel</span></a>
                        <a href="{{ route('admin.monitoring-laporan.export', ['format' => 'csv'] + request()->query()) }}" class="pkm-secondary-button"><i data-lucide="download" class="size-4"></i><span>Unduh CSV</span></a>
                    </div>
                    <a href="{{ route('admin.monitoring-laporan') }}" class="pkm-secondary-button"><i data-lucide="rotate-ccw" class="size-4"></i><span>Reset</span></a>
                    <button type="submit" class="pkm-primary-button"><i data-lucide="funnel" class="size-4"></i><span>Terapkan Filter</span></button>
                </div>
            </form>
        </section>

        <section class="pkm-card pkm-table-card">
            <div class="pkm-card__head">
                <div>
                    <h3 style="font-weight: bold">Daftar Laporan</h3>
                    <br>
                </div>
            </div>

            @if ($reports->isEmpty())
                <div class="pkm-empty-state">
                    <strong>Belum ada laporan kegiatan pada filter ini.</strong>
                </div>
            @else
                <div class="pkm-table pkm-table--laporan">
                    <div class="pkm-table__head">
                        <span>Laporan</span>
                        <span>Pelaksana</span>
                        <span>Jadwal</span>
                        <span>Waktu Dibuat</span>
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
