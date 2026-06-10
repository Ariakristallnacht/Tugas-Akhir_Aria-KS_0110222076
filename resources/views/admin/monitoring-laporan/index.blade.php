@extends('layouts.dashboard')

@php
    $title = $title ?? 'Monitoring Laporan Kegiatan | Puskesmas Bunar';
    $heading = $heading ?? 'Monitoring Laporan Kegiatan';
    $routeName = $routeName ?? 'admin.monitoring-laporan';
@endphp

@push('styles')
    <style>
        @media (min-width: 1280px) {
            .pkm-management-summary--single-row {
                grid-template-columns: repeat(3, minmax(0, 1fr));
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

        .pkm-report-search-inline.is-loading .pkm-input {
            opacity: 0.7;
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

        @media (max-width: 1180px) {
            .pkm-management-summary--single-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .pkm-monitoring-filter .pkm-form-grid {
                grid-template-columns: 1fr;
            }

            .pkm-monitoring-filter .pkm-form-actions {
                justify-content: stretch;
            }

            .pkm-monitoring-filter .pkm-form-actions > * {
                flex: 1 1 0;
            }

            .pkm-report-table-head {
                align-items: stretch;
                flex-direction: column;
            }

            .pkm-report-search-inline {
                width: 100%;
                margin-left: 0;
            }
        }

        @media (max-width: 640px) {
            .pkm-management-summary--single-row {
                grid-template-columns: 1fr;
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

        <div class="pkm-management-summary pkm-management-summary--single-row">
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-cyan-100 text-cyan-700"><i data-lucide="file-text" class="size-5"></i></div>
                <div class="pkm-metric-card__value" id="summary-all">{{ $summary['all'] }}</div>
                <div class="pkm-metric-card__label">Total Laporan</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-amber-100 text-amber-700"><i data-lucide="users-round" class="size-5"></i></div>
                <div class="pkm-metric-card__value" id="summary-pegawai">{{ $summary['pegawai'] }}</div>
                <div class="pkm-metric-card__label">Pegawai Pelaksana</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-emerald-100 text-emerald-700"><i data-lucide="folder-kanban" class="size-5"></i></div>
                <div class="pkm-metric-card__value" id="summary-kegiatan">{{ $summary['kegiatan'] }}</div>
                <div class="pkm-metric-card__label">Jenis Kegiatan</div>
            </article>
        </div>

        <section class="pkm-card">
            <div class="pkm-card__head">
                <div>
                    <h3 style="font-weight: bold">Filter</h3>
                    <br>
                </div>
            </div>

            <form method="GET" action="{{ route($routeName) }}" class="pkm-monitoring-filter">
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
                    <a href="{{ route($routeName) }}" class="pkm-secondary-button"><span>Reset</span></a>
                    <button type="submit" class="pkm-primary-button"><span>Filter</span></button>
                </div>
            </form>
        </section>

        <section class="pkm-card pkm-table-card">
            <div class="pkm-card__head">
                <div class="pkm-report-table-head">
                    <div>
                        <h3 style="font-weight: bold">Daftar Laporan</h3>
                    </div>
                    <form method="GET" action="{{ route($routeName) }}" class="pkm-report-search-inline" id="report-search-form">
                        <input type="hidden" name="date_from" value="{{ $filters['date_from'] }}">
                        <input type="hidden" name="date_to" value="{{ $filters['date_to'] }}">
                        <input type="hidden" name="pegawai_id" value="{{ $filters['pegawai_id'] }}">
                        <input id="table-search" class="pkm-input" type="text" name="search" value="{{ $filters['search'] }}" placeholder="Cari nama pegawai, kegiatan, atau lokasi" autocomplete="off">
                    </form>
                </div>
            </div>

            <div id="report-list-container">
                @include('admin.monitoring-laporan._report-list', ['reports' => $reports, 'showRouteName' => $showRouteName ?? null])
            </div>
        </section>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchForm = document.getElementById('report-search-form');
            const searchInput = document.getElementById('table-search');
            const resultContainer = document.getElementById('report-list-container');
            const summaryAll = document.getElementById('summary-all');
            const summaryPegawai = document.getElementById('summary-pegawai');
            const summaryKegiatan = document.getElementById('summary-kegiatan');

            if (!searchForm || !searchInput || !resultContainer) {
                return;
            }

            let debounceTimer;
            let activeController = null;

            const loadResults = (targetUrl = null) => {
                const url = targetUrl ? new URL(targetUrl, window.location.origin) : new URL(searchForm.action);

                if (!targetUrl) {
                    const formData = new FormData(searchForm);
                    url.search = new URLSearchParams(formData).toString();
                }

                if (activeController) {
                    activeController.abort();
                }

                activeController = new AbortController();
                searchForm.classList.add('is-loading');

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                    signal: activeController.signal,
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error('Gagal memuat hasil pencarian.');
                        }

                        return response.json();
                    })
                    .then((data) => {
                        resultContainer.innerHTML = data.html ?? '';
                        if (data.summary) {
                            if (summaryAll) {
                                summaryAll.textContent = data.summary.all ?? 0;
                            }

                            if (summaryPegawai) {
                                summaryPegawai.textContent = data.summary.pegawai ?? 0;
                            }

                            if (summaryKegiatan) {
                                summaryKegiatan.textContent = data.summary.kegiatan ?? 0;
                            }
                        }

                        window.history.replaceState({}, '', url);

                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    })
                    .catch((error) => {
                        if (error.name !== 'AbortError') {
                            console.error(error);
                        }
                    })
                    .finally(() => {
                        searchForm.classList.remove('is-loading');
                    });
            };

            searchForm.addEventListener('submit', function (event) {
                event.preventDefault();
                loadResults();
            });

            searchInput.addEventListener('input', function () {
                window.clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(loadResults, 350);
            });

            resultContainer.addEventListener('click', function (event) {
                const link = event.target.closest('.pkm-pagination a');

                if (!link) {
                    return;
                }

                event.preventDefault();
                loadResults(link.href);
            });
        });
    </script>
@endpush
