@extends('layouts.dashboard')

@php
    $title = 'Pengajuan Dinas Luar | Puskesmas Bunar';
    $heading = 'Pengajuan Dinas Luar';
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
                <h2>Data Pengajuan Dinas Luar</h2>
                <p>Pegawai dapat melihat riwayat pengajuan pribadi sebelum membuat pengajuan baru untuk kegiatan dinas luar.</p>
            </div>
            <a href="{{ route('pegawai.pengajuan-dinas.create') }}" class="pkm-primary-button">Tambah Pengajuan</a>
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
                    <h3>Riwayat Pengajuan Saya</h3>
                    <p>Pengajuan yang masih berstatus diajukan dapat diubah atau dihapus sebelum diverifikasi PJ penjadwalan.</p>
                </div>
            </div>

            @if ($submissions->isEmpty())
                <div class="pkm-empty-state">
                    <strong>Belum ada pengajuan dinas luar.</strong>
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
                            $isEditable = in_array($submission->status, ['diajukan', 'dibatalkan'], true);
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
                                    @if ($isEditable)
                                        <a href="{{ route('pegawai.pengajuan-dinas.edit', $submission) }}" class="pkm-text-link">Edit</a>
                                        <form method="POST" action="{{ route('pegawai.pengajuan-dinas.destroy', $submission) }}" onsubmit="return confirm('Hapus pengajuan dinas luar ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="pkm-danger-button">Hapus</button>
                                        </form>
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
                        <span class="pkm-pagination__muted">Sebelumnya</span>
                    @else
                        <a href="{{ $submissions->previousPageUrl() }}" class="pkm-secondary-button">Sebelumnya</a>
                    @endif

                    <span>Halaman {{ $submissions->currentPage() }} dari {{ $submissions->lastPage() }}</span>

                    @if ($submissions->hasMorePages())
                        <a href="{{ $submissions->nextPageUrl() }}" class="pkm-secondary-button">Berikutnya</a>
                    @else
                        <span class="pkm-pagination__muted">Berikutnya</span>
                    @endif
                </div>
            @endif
        </section>
    </section>
@endsection
