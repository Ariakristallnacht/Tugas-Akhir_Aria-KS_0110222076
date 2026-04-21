@extends('layouts.dashboard')

@php
    $title = 'Data Pegawai | Puskesmas Bunar';
    $heading = 'Data Pegawai';
@endphp

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-section-head">
            <div>
                <h2 style="font-weight: bold">Kelola Pegawai</h2>
            </div>
            <a href="{{ route('admin.pegawai.create') }}" class="pkm-primary-button">Tambah Pegawai</a>
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
                <div class="pkm-table pkm-table--pegawai">
                    <div class="pkm-table__head">
                        <span>Pegawai</span>
                        <span>Jabatan</span>
                        <span>Akun Login</span>
                        <span>Status</span>
                        <span>Aksi</span>
                    </div>

                    @foreach ($pegawaiList as $pegawai)
                        <div class="pkm-table__row">
                            <div data-label="Pegawai">
                                <strong>{{ $pegawai->nama }}</strong>
                                <small>{{ $pegawai->nip }}</small>
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
                                    <a href="{{ route('admin.pegawai.edit', $pegawai) }}" class="pkm-text-link">Edit</a>
                                    <form method="POST" action="{{ route('admin.pegawai.destroy', $pegawai) }}" onsubmit="return confirm('Hapus data pegawai ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="pkm-danger-button">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pkm-pagination">
                    @if ($pegawaiList->onFirstPage())
                        <span class="pkm-pagination__muted">Sebelumnya</span>
                    @else
                        <a href="{{ $pegawaiList->previousPageUrl() }}" class="pkm-secondary-button">Sebelumnya</a>
                    @endif

                    <span>Halaman {{ $pegawaiList->currentPage() }} dari {{ $pegawaiList->lastPage() }}</span>

                    @if ($pegawaiList->hasMorePages())
                        <a href="{{ $pegawaiList->nextPageUrl() }}" class="pkm-secondary-button">Berikutnya</a>
                    @else
                        <span class="pkm-pagination__muted">Berikutnya</span>
                    @endif
                </div>
            @endif
        </section>
    </section>
@endsection
