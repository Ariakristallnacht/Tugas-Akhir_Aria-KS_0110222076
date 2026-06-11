@extends('layouts.dashboard')

@php
    $title = 'Pengajuan Dinas | Puskesmas Bunar';
    $heading = 'Pengajuan Dinas Luar';
@endphp

@push('styles')
    <style>
        .pkm-dashboard-main--pengajuan-dinas {
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
            .pkm-dashboard-main--pengajuan-dinas {
                gap: 20px;
            }

            .pkm-table--pegawai .pkm-table__head,
            .pkm-table--pegawai .pkm-table__row {
                grid-template-columns: 1fr;
            }

            .pkm-table--pegawai .pkm-table__head {
                display: none;
            }

            .pkm-table--pegawai {
                gap: 12px;
                border: 0;
                background: transparent;
            }

            .pkm-table--pegawai .pkm-table__row {
                gap: 10px;
                padding: 16px;
                border: 1px solid var(--pkm-border);
                border-radius: 18px;
                box-shadow: 0 10px 28px rgba(58, 78, 113, 0.06);
            }

            .pkm-table--pegawai .pkm-table__row > div {
                display: flex;
                flex-direction: column;
                gap: 4px;
                min-width: 0;
            }

            .pkm-table--pegawai .pkm-table__row > div::before {
                content: attr(data-label);
                font-size: 0.74rem;
                font-weight: 700;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                color: #8b9ab0;
            }

            .pkm-table--pegawai .pkm-table__row > [data-label="Status"],
            .pkm-table--pegawai .pkm-table__row > [data-label="Aksi"] {
                align-items: flex-start;
            }

            .pkm-table--pegawai .pkm-table__row > [data-label="Aksi"] .pkm-row-actions {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .pkm-table--pegawai .pkm-table__row > [data-label="Aksi"] .pkm-row-actions > * {
                flex: 1 1 220px;
            }
        }

        @media (max-width: 640px) {
            .pkm-dashboard-main--pengajuan-dinas {
                gap: 18px;
            }

            .pkm-table--pegawai .pkm-table__row {
                padding: 14px;
            }

            .pkm-table--pegawai .pkm-table__row strong,
            .pkm-table--pegawai .pkm-table__row small {
                overflow-wrap: anywhere;
                word-break: break-word;
            }

            .pkm-table--pegawai .pkm-row-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .pkm-table--pegawai .pkm-row-actions > * {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <section class="pkm-dashboard-main pkm-dashboard-main--pengajuan-dinas">
        <div class="pkm-section-head">
            <div>
                <h2 style="font-weight: bold">Pengajuan Dinas Luar</h2>
            </div>
            <a href="{{ route('pegawai.pengajuan-dinas.create') }}" class="pkm-primary-button">
                <i data-lucide="plus" class="size-4"></i>
                <span>Tambah Pengajuan</span>
            </a>
        </div>

        @include('admin.partials.flash')

        <div class="pkm-management-summary pkm-management-summary--single-row">
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-cyan-100 text-cyan-700"><i data-lucide="files" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['total'] }}</div>
                <div class="pkm-metric-card__label">Total Pengajuan</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-amber-100 text-amber-700"><i data-lucide="clock-3" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['pending'] }}</div>
                <div class="pkm-metric-card__label">Menunggu Verifikasi</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-emerald-100 text-emerald-700"><i data-lucide="badge-check" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['approved'] }}</div>
                <div class="pkm-metric-card__label">Disetujui</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-rose-100 text-rose-700"><i data-lucide="badge-x" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['rejected'] }}</div>
                <div class="pkm-metric-card__label">Ditolak</div>
            </article>
        </div>

        <section class="pkm-card pkm-table-card">
            <div class="pkm-card__head">
                <div>
                    <h3 style="font-weight:bold">Riwayat Pengajuan Saya</h3>
                </div>
            </div>

            @if ($submissions->isEmpty())
                <div class="pkm-empty-state">
                    <strong>Belum ada Pengajuan Dinas.</strong>
                    <p>Buat pengajuan pertama agar PJ penjadwalan bisa menyesuaikan jadwal layanan.</p>
                </div>
            @else
                <div class="pkm-table pkm-table--pegawai">
                    <div class="pkm-table__head">
                        <span>Tanggal</span>
                        <span>Tujuan</span>
                        <span>Kegiatan</span>
                        <span>Status</span>
                        <span>Aksi</span>
                    </div>

                    @foreach ($submissions as $submission)
                        @php
                            $canEdit = in_array($submission->status, ['diajukan', 'dibatalkan', 'disetujui'], true);
                            $canDelete = in_array($submission->status, ['diajukan', 'dibatalkan'], true);
                            $statusClass = match ($submission->status) {
                                'disetujui' => 'is-green',
                                'dibatalkan' => 'is-blue',
                                default => 'is-amber',
                            };
                        @endphp
                        <div class="pkm-table__row">
                            <div data-label="Tanggal">
                                <strong>{{ $submission->tanggal_mulai->translatedFormat('d M Y') }}</strong>
                                <small>
                                    {{ $submission->tanggal_selesai->equalTo($submission->tanggal_mulai) ? 'Satu hari' : 's.d. '.$submission->tanggal_selesai->translatedFormat('d M Y') }}
                                </small>
                            </div>
                            <div data-label="Tujuan">
                                <strong>{{ $submission->tujuan }}</strong>
                                <small>Diajukan {{ $submission->tanggal_pengajuan->translatedFormat('d M Y') }}</small>
                            </div>
                            <div data-label="Kegiatan">
                                <strong>{{ \Illuminate\Support\Str::limit($submission->kegiatan, 70) }}</strong>
                                <small>{{ \Illuminate\Support\Str::limit($submission->keterangan ?: 'Tanpa keterangan tambahan', 60) }}</small>
                            </div>
                            <div data-label="Status">
                                <span class="pkm-pill {{ $statusClass }}">{{ ucfirst($submission->status) }}</span>
                            </div>
                            <div data-label="Aksi">
                                <div class="pkm-row-actions">
                                    @if ($canEdit)
                                        <a href="{{ route('pegawai.pengajuan-dinas.edit', $submission) }}" class="pkm-text-link">
                                            <i data-lucide="pencil" class="size-4"></i>
                                            <span>Edit</span>
                                        </a>

                                        @if ($canDelete)
                                            <form method="POST" action="{{ route('pegawai.pengajuan-dinas.destroy', $submission) }}" onsubmit="return confirm('Hapus Pengajuan Dinas ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="pkm-danger-button">
                                                    <i data-lucide="trash-2" class="size-4"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="pkm-pagination__muted">Terkunci</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pkm-pagination">
                    @if ($submissions->onFirstPage())
                        <span class="pkm-pagination__muted" aria-hidden="true"><i data-lucide="chevron-left" class="size-4"></i></span>
                    @else
                        <a href="{{ $submissions->previousPageUrl() }}" class="pkm-secondary-button" aria-label="Sebelumnya">
                            <i data-lucide="chevron-left" class="size-4"></i>
                        </a>
                    @endif

                    <span class="pkm-pagination__page">{{ $submissions->currentPage() }} / {{ $submissions->lastPage() }}</span>

                    @if ($submissions->hasMorePages())
                        <a href="{{ $submissions->nextPageUrl() }}" class="pkm-secondary-button" aria-label="Berikutnya">
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
