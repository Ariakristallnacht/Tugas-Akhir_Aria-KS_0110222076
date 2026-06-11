@extends('layouts.dashboard')

@php
    $title = 'Kelola Pegawai | Puskesmas Bunar';
    $heading = 'Kelola Pegawai';
@endphp

@push('styles')
    <style>
        .pkm-dashboard-main--pegawai-directory {
            display: flex;
            flex-direction: column;
            gap: 24px;
            min-width: 0;
        }

        @media (min-width: 1024px) {
            .pkm-table--pegawai-directory .pkm-table__head,
            .pkm-table--pegawai-directory .pkm-table__row {
                grid-template-columns:
                    minmax(0, 1.1fr)
                    minmax(220px, 0.95fr)
                    minmax(260px, 1.08fr)
                    minmax(110px, 0.45fr)
                    minmax(190px, 0.7fr);
            }

            .pkm-table--pegawai-directory .pkm-table__head > :nth-child(4),
            .pkm-table--pegawai-directory .pkm-table__head > :nth-child(5),
            .pkm-table--pegawai-directory .pkm-table__row > :nth-child(4),
            .pkm-table--pegawai-directory .pkm-table__row > :nth-child(5) {
                justify-self: center;
            }

            .pkm-table--pegawai-directory .pkm-table__row > :nth-child(5) {
                width: 100%;
            }

            .pkm-table--pegawai-directory .pkm-table__row > [data-label="Aksi"] .pkm-row-actions {
                justify-content: center;
                flex-wrap: nowrap;
            }
        }

        @media (max-width: 1023px) {
            .pkm-dashboard-main--pegawai-directory {
                gap: 20px;
            }

            .pkm-table--pegawai-directory .pkm-table__head,
            .pkm-table--pegawai-directory .pkm-table__row {
                grid-template-columns: 1fr;
            }

            .pkm-table--pegawai-directory .pkm-table__head {
                display: none;
            }

            .pkm-table--pegawai-directory {
                gap: 12px;
                border: 0;
                background: transparent;
            }

            .pkm-table--pegawai-directory .pkm-table__row {
                gap: 10px;
                padding: 16px;
                border: 1px solid var(--pkm-border);
                border-radius: 18px;
                box-shadow: 0 10px 28px rgba(58, 78, 113, 0.06);
            }

            .pkm-table--pegawai-directory .pkm-table__row > div {
                display: flex;
                flex-direction: column;
                gap: 4px;
                min-width: 0;
            }

            .pkm-table--pegawai-directory .pkm-table__row > div::before {
                content: attr(data-label);
                font-size: 0.74rem;
                font-weight: 700;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                color: #8b9ab0;
            }

            .pkm-table--pegawai-directory .pkm-table__row > [data-label="Status"],
            .pkm-table--pegawai-directory .pkm-table__row > [data-label="Aksi"] {
                align-items: flex-start;
            }

            .pkm-table--pegawai-directory .pkm-table__row > [data-label="Aksi"] .pkm-row-actions {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .pkm-table--pegawai-directory .pkm-table__row > [data-label="Aksi"] .pkm-row-actions > * {
                flex: 1 1 220px;
            }
        }

        @media (max-width: 640px) {
            .pkm-dashboard-main--pegawai-directory {
                gap: 18px;
            }

            .pkm-table--pegawai-directory .pkm-table__row {
                padding: 14px;
            }

            .pkm-table--pegawai-directory .pkm-table__row strong,
            .pkm-table--pegawai-directory .pkm-table__row small {
                overflow-wrap: anywhere;
                word-break: break-word;
            }

            .pkm-table--pegawai-directory .pkm-row-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .pkm-table--pegawai-directory .pkm-row-actions > * {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <section class="pkm-dashboard-main pkm-dashboard-main--pegawai-directory">
        <div class="pkm-section-head">
            <div>
                <h2 style="font-weight: bold">Kelola Pegawai</h2>
            </div>
            <a href="{{ route('admin.pegawai.create') }}" class="pkm-primary-button">
                <i data-lucide="plus" class="size-4"></i>
                <span>Tambah Pegawai</span>
            </a>
        </div>

        @include('admin.partials.flash')

        <div class="pkm-management-summary">
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-emerald-100 text-emerald-700"><i data-lucide="briefcase-business" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $pegawaiList->total() }}</div>
                <div class="pkm-metric-card__label">Total Pegawai</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-cyan-100 text-cyan-700"><i data-lucide="badge-check" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $pegawaiList->where('is_aktif', true)->count() }}</div>
                <div class="pkm-metric-card__label">Aktif di Halaman Ini</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-amber-100 text-amber-700"><i data-lucide="link" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $pegawaiList->whereNotNull('user')->count() }}</div>
                <div class="pkm-metric-card__label">Pegawai Dengan Akun</div>
            </article>
        </div>

        <section class="pkm-card pkm-table-card">
            <div class="pkm-card__head">
                <div>
                    <h3 style="font-weight: bold">Daftar Pegawai dan Akun</h3>
                </div>
            </div>

            @if ($pegawaiList->isEmpty())
                <div class="pkm-empty-state">
                    <strong>Belum ada data pegawai.</strong>
                    <p>Tambahkan pegawai baru untuk mulai membangun data operasional.</p>
                </div>
            @else
                <div class="pkm-table pkm-table--pegawai pkm-table--pegawai-directory">
                    <div class="pkm-table__head">
                        <span>Pegawai</span>
                        <span>Jabatan</span>
                        <span>Akun Login</span>
                        <span>Status</span>
                        <span>Aksi</span>
                    </div>

                    @foreach ($pegawaiList as $pegawai)
                        @php
                            $jenisPegawaiLabel = match ($pegawai->jenis_pegawai ?? 'asn') {
                                'asn' => 'PNS',
                                'p3k' => 'PPPK',
                                'honorer' => 'Honorer',
                                default => strtoupper((string) $pegawai->jenis_pegawai),
                            };
                        @endphp
                        <div class="pkm-table__row">
                            <div data-label="Pegawai">
                                <strong>{{ $pegawai->nama }}</strong>
                                <small>{{ $jenisPegawaiLabel }} · {{ $pegawai->nip ?: 'NIP belum diisi' }}</small>
                            </div>
                            <div data-label="Jabatan">
                                <strong>{{ $pegawai->jabatan }}</strong>
                                <small>{{ $pegawai->unit_kerja }}</small>
                            </div>
                            <div data-label="Akun Login">
                                @if ($pegawai->user)
                                    <strong>{{ $pegawai->user->email }}</strong>
                                    <small>{{ $pegawai->user->role?->nama ?? 'Role belum diatur' }}</small>
                                @else
                                    <strong>Belum punya akun</strong>
                                    <small>{{ $pegawai->no_hp ?: 'No. HP belum diisi' }}</small>
                                @endif
                            </div>
                            <div data-label="Status">
                                <span class="pkm-pill {{ $pegawai->is_aktif ? 'is-green' : 'is-amber' }}">
                                    {{ $pegawai->is_aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <div data-label="Aksi">
                                <div class="pkm-row-actions">
                                    <a href="{{ route('admin.pegawai.edit', $pegawai) }}" class="pkm-text-link">
                                        <i data-lucide="pencil" class="size-4"></i>
                                        <span>Edit</span>
                                    </a>
                                    <form method="POST" action="{{ route('admin.pegawai.destroy', $pegawai) }}" onsubmit="return confirm('Hapus data pegawai ini?')">
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
                    @if ($pegawaiList->onFirstPage())
                        <span class="pkm-pagination__muted" aria-hidden="true"><i data-lucide="chevron-left" class="size-4"></i></span>
                    @else
                        <a href="{{ $pegawaiList->previousPageUrl() }}" class="pkm-secondary-button" aria-label="Sebelumnya">
                            <i data-lucide="chevron-left" class="size-4"></i>
                        </a>
                    @endif

                    <span class="pkm-pagination__page">{{ $pegawaiList->currentPage() }} / {{ $pegawaiList->lastPage() }}</span>

                    @if ($pegawaiList->hasMorePages())
                        <a href="{{ $pegawaiList->nextPageUrl() }}" class="pkm-secondary-button" aria-label="Berikutnya">
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
