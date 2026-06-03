@extends('layouts.dashboard')

@php
    $title = 'Verifikasi Dinas Luar | Puskesmas Bunar';
    $heading = 'Verifikasi Dinas Luar';
@endphp

@push('styles')
    <style>
        .pkm-table--verifikasi-pengajuan .pkm-table__head,
        .pkm-table--verifikasi-pengajuan .pkm-table__row {
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr) minmax(150px, 0.8fr) minmax(120px, 0.7fr) minmax(88px, 0.45fr);
        }

        .pkm-table--verifikasi-pengajuan .pkm-table__head > :last-child,
        .pkm-table--verifikasi-pengajuan .pkm-table__row > :last-child {
            justify-self: start;
        }

        .pkm-submission-note {
            margin-top: 14px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(191, 138, 0, 0.18);
            background: #fff9e8;
        }

        .pkm-submission-note.is-info {
            border-color: rgba(37, 99, 235, 0.18);
            background: #eff6ff;
        }

        .pkm-submission-note strong,
        .pkm-submission-note span {
            display: block;
        }

        .pkm-submission-note span + span {
            margin-top: 4px;
        }

        .pkm-submission-description {
            margin-top: 14px;
            white-space: pre-line;
        }
    </style>
@endpush

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-section-head">
            <div>
                <h2 style="font-weight: bold">Verifikasi Dinas Luar</h2>
            </div>
        </div>

        @include('admin.partials.flash')

        <div class="pkm-management-summary">
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-cyan-100 text-cyan-700"><i data-lucide="files" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['all'] }}</div>
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
        </div>

        <section class="pkm-card pkm-table-card">
            <div class="pkm-card__head">
                <div>
                    <h3 style="font-weight: bold">Daftar Pengajuan Pegawai</h3>
                </div>
            </div>

            @if ($submissions->isEmpty())
                <div class="pkm-empty-state">
                    <strong>Belum ada Pengajuan Dinas.</strong>
                    <p>Pengajuan pegawai akan tampil di halaman ini untuk diverifikasi.</p>
                </div>
            @else
                <div class="pkm-table pkm-table--pegawai pkm-table--verifikasi-pengajuan">
                    <div class="pkm-table__head">
                        <span>Pegawai</span>
                        <span>Pengajuan</span>
                        <span>Tanggal</span>
                        <span>Status</span>
                        <span>Aksi</span>
                    </div>

                    @foreach ($submissions as $submission)
                        @php
                            $statusClass = match ($submission->status) {
                                'disetujui' => 'is-green',
                                'ditolak' => 'is-amber',
                                'dibatalkan' => 'is-blue',
                                default => 'is-amber',
                            };
                            $modalId = 'verifikasi-pengajuan-'.$submission->id;
                            $keteranganLines = collect(preg_split("/\r\n|\n|\r/", (string) $submission->keterangan) ?: [])
                                ->map(fn (string $line) => trim($line))
                                ->filter();
                            $alasanPerubahan = $keteranganLines
                                ->first(fn (string $line) => \Illuminate\Support\Str::startsWith($line, 'Alasan perubahan tanggal: '));
                            $tanggalSebelumnya = $keteranganLines
                                ->first(fn (string $line) => \Illuminate\Support\Str::startsWith($line, 'Kegiatan ini akan dilaksanakan pada '));
                            $alasanPengajuanKembali = $alasanPerubahan
                                ? \Illuminate\Support\Str::after($alasanPerubahan, 'Alasan perubahan tanggal: ')
                                : null;
                            $keteranganUtama = $keteranganLines
                                ->reject(fn (string $line) => \Illuminate\Support\Str::startsWith($line, 'Alasan perubahan tanggal: '))
                                ->reject(fn (string $line) => \Illuminate\Support\Str::startsWith($line, 'Kegiatan ini akan dilaksanakan pada '))
                                ->implode("\n");
                            $pernahDiperbarui = $submission->updated_at
                                && $submission->created_at
                                && $submission->updated_at->gt($submission->created_at);
                            $isPengajuanRevisi = filled($alasanPerubahan)
                                || filled($tanggalSebelumnya)
                                || ($submission->status === 'diajukan' && $pernahDiperbarui);
                            $tanggalKegiatanTerbaru = $submission->tanggal_mulai->isSameDay($submission->tanggal_selesai)
                                ? $submission->tanggal_mulai->translatedFormat('d M Y')
                                : $submission->tanggal_mulai->translatedFormat('d M Y').' s.d. '.$submission->tanggal_selesai->translatedFormat('d M Y');
                        @endphp
                        <div class="pkm-table__row">
                            <div data-label="Pegawai">
                                <strong>{{ $submission->pegawai?->nama ?? 'Pegawai tidak ditemukan' }}</strong>
                                <small>{{ $submission->pegawai?->jabatan ?? 'Jabatan tidak tersedia' }}</small>
                            </div>
                            <div data-label="Pengajuan">
                                <strong>{{ \Illuminate\Support\Str::limit($submission->kegiatan, 55) }}</strong>
                                <small>{{ $submission->tujuan }}</small>
                                @if ($isPengajuanRevisi)
                                    <small>Pengajuan ini memuat perubahan tanggal kegiatan.</small>
                                @endif
                            </div>
                            <div data-label="Tanggal">
                                <strong>{{ $submission->tanggal_mulai->translatedFormat('d M Y') }}</strong>
                                <small>{{ $submission->tanggal_selesai->translatedFormat('d M Y') }}</small>
                            </div>
                            <div data-label="Status">
                                <span class="pkm-pill {{ $statusClass }}">{{ ucfirst($submission->status) }}</span>
                            </div>
                            <div data-label="Aksi">
                                <button type="button" class="pkm-text-link" data-open-modal="{{ $modalId }}" aria-label="Verifikasi pengajuan {{ $submission->pegawai?->nama }}">
                                    <i data-lucide="square-pen" class="size-4"></i>
                                    <span>Verifikasi</span>
                                </button>
                            </div>
                        </div>

                        <div class="pkm-modal" id="{{ $modalId }}" hidden>
                            <div class="pkm-modal__backdrop" data-modal-close></div>
                            <div class="pkm-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title">
                                <div class="pkm-modal__head">
                                    <div>
                                        <h3 id="{{ $modalId }}-title">Verifikasi Pengajuan Dinas</h3>
                                        <p>{{ $submission->pegawai?->nama ?? 'Pegawai tidak ditemukan' }}</p>
                                    </div>
                                    <button type="button" class="pkm-modal__close" data-modal-close aria-label="Tutup modal verifikasi">
                                        <i data-lucide="x" class="size-4"></i>
                                    </button>
                                </div>

                                <div class="pkm-modal__summary">
                                    <span class="pkm-pill {{ $statusClass }}">{{ ucfirst($submission->status) }}</span>
                                    <small>
                                        Diajukan {{ $submission->tanggal_pengajuan->translatedFormat('d M Y') }}
                                        @if ($submission->verifier)
                                            oleh {{ $submission->verifier->name }}
                                        @endif
                                    </small>
                                </div>

                                <div class="pkm-modal__body">
                                    <article class="pkm-modal-schedule">
                                        <div class="pkm-modal-schedule__grid">
                                            <div>
                                                <span>Pegawai</span>
                                                <strong>{{ $submission->pegawai?->nama ?? '-' }}</strong>
                                            </div>
                                            <div>
                                                <span>Jabatan</span>
                                                <strong>{{ $submission->pegawai?->jabatan ?? '-' }}</strong>
                                            </div>
                                            <div>
                                                <span>Tanggal mulai</span>
                                                <strong>{{ $submission->tanggal_mulai->translatedFormat('d M Y') }}</strong>
                                            </div>
                                            <div>
                                                <span>Tanggal selesai</span>
                                                <strong>{{ $submission->tanggal_selesai->translatedFormat('d M Y') }}</strong>
                                            </div>
                                            <div>
                                                <span>Tujuan</span>
                                                <strong>{{ $submission->tujuan }}</strong>
                                            </div>
                                            <div>
                                                <span>Kegiatan</span>
                                                <strong>{{ $submission->kegiatan }}</strong>
                                            </div>
                                            <div>
                                                <span>Bukti surat</span>
                                                @if ($submission->bukti_surat_path)
                                                    <strong>
                                                        <a href="{{ $submission->bukti_surat_url }}" target="_blank" rel="noopener noreferrer" class="pkm-text-link">
                                                            <i data-lucide="{{ $submission->bukti_surat_is_pdf ? 'file-text' : 'image' }}" class="size-4"></i>
                                                            <span>{{ $submission->bukti_surat_nama ?? 'Lihat lampiran' }}</span>
                                                        </a>
                                                    </strong>
                                                @else
                                                    <strong>Tidak ada lampiran</strong>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($isPengajuanRevisi)
                                            <div class="pkm-submission-note is-info">
                                                <strong>Pengajuan diajukan kembali</strong>
                                                <span>Dinas ini telah diperbarui oleh pegawai dan diajukan ulang untuk diverifikasi kembali oleh PJ.</span>
                                            </div>

                                            <div class="pkm-modal-schedule__grid" style="margin-top: 14px;">
                                                <div>
                                                    <span>Tanggal diajukan kembali</span>
                                                    <strong>{{ $submission->updated_at?->translatedFormat('d M Y') ?? '-' }}</strong>
                                                </div>
                                                <div>
                                                    <span>Tanggal kegiatan terbaru</span>
                                                    <strong>{{ $tanggalKegiatanTerbaru }}</strong>
                                                </div>
                                                <div style="grid-column: 1 / -1;">
                                                    <span>Keterangan pengajuan kembali</span>
                                                    <strong>{{ $alasanPengajuanKembali ?: 'Pegawai mengajukan kembali setelah melakukan perubahan pada pengajuan dinas.' }}</strong>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($isPengajuanRevisi)
                                            <div class="pkm-submission-note">
                                                <strong>Catatan perubahan pengajuan</strong>
                                                @if ($alasanPerubahan)
                                                    <span>{{ $alasanPerubahan }}</span>
                                                @endif
                                                @if ($tanggalSebelumnya)
                                                    <span>{{ $tanggalSebelumnya }}</span>
                                                @endif
                                            </div>
                                        @endif

                                        @if ($keteranganUtama !== '')
                                            <p class="pkm-submission-description">{{ $keteranganUtama }}</p>
                                        @elseif ($submission->keterangan)
                                            <p class="pkm-submission-description">{{ $submission->keterangan }}</p>
                                        @endif
                                    </article>

                                    <form method="POST" action="{{ route('pj.verifikasi-pengajuan-dinas.update', $submission) }}" class="pkm-form-stack">
                                        @csrf
                                        @method('PATCH')

                                        <div class="pkm-form-grid">
                                            <div class="pkm-field pkm-field--full">
                                                <label for="status_{{ $submission->id }}">Keputusan verifikasi</label>
                                                <select id="status_{{ $submission->id }}" class="pkm-input" name="status" required>
                                                    <option value="">Pilih keputusan</option>
                                                    <option value="disetujui" @selected(old('status') === 'disetujui' && (string) old('pengajuan_id') === (string) $submission->id)>Setujui</option>
                                                    <option value="ditolak" @selected(old('status') === 'ditolak' && (string) old('pengajuan_id') === (string) $submission->id)>Tolak</option>
                                                </select>
                                            </div>

                                            <div class="pkm-field pkm-field--full">
                                                <label for="catatan_verifikasi_{{ $submission->id }}">Catatan verifikasi</label>
                                                <textarea id="catatan_verifikasi_{{ $submission->id }}" class="pkm-input" name="catatan_verifikasi" rows="4" placeholder="Tambahkan catatan bila diperlukan.">{{ (string) old('pengajuan_id') === (string) $submission->id ? old('catatan_verifikasi') : $submission->catatan_verifikasi }}</textarea>
                                            </div>
                                        </div>

                                        <input type="hidden" name="pengajuan_id" value="{{ $submission->id }}">

                                        <div class="pkm-form-actions">
                                            <button type="button" class="pkm-secondary-button" data-modal-close>
                                                <i data-lucide="x" class="size-4"></i>
                                                <span>Tutup</span>
                                            </button>
                                            <button type="submit" class="pkm-primary-button">
                                                <i data-lucide="save" class="size-4"></i>
                                                <span>Simpan Verifikasi</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pkm-pagination">
                    @if ($submissions->onFirstPage())
                        <span class="pkm-pagination__muted">Sebelumnya</span>
                    @else
                        <a href="{{ $submissions->previousPageUrl() }}" class="pkm-secondary-button"><i data-lucide="chevron-left" class="size-4"></i><span>Sebelumnya</span></a>
                    @endif

                    <span>Halaman {{ $submissions->currentPage() }} dari {{ $submissions->lastPage() }}</span>

                    @if ($submissions->hasMorePages())
                        <a href="{{ $submissions->nextPageUrl() }}" class="pkm-secondary-button"><span>Berikutnya</span><i data-lucide="chevron-right" class="size-4"></i></a>
                    @else
                        <span class="pkm-pagination__muted">Berikutnya</span>
                    @endif
                </div>
            @endif
        </section>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalToReopen = @json($errors->any() ? 'verifikasi-pengajuan-'.old('pengajuan_id') : null);
            const openButtons = document.querySelectorAll('[data-open-modal]');
            const closeButtons = document.querySelectorAll('[data-modal-close]');

            const closeModal = (modal) => {
                if (!modal) {
                    return;
                }

                modal.hidden = true;
                document.body.classList.remove('pkm-modal-open');
            };

            const openModal = (modal) => {
                if (!modal) {
                    return;
                }

                modal.hidden = false;
                document.body.classList.add('pkm-modal-open');

                if (window.lucide) {
                    window.lucide.createIcons();
                }
            };

            openButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    const modal = document.getElementById(this.dataset.openModal);
                    openModal(modal);
                });
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    closeModal(this.closest('.pkm-modal'));
                });
            });

            document.querySelectorAll('.pkm-modal').forEach((modal) => {
                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        closeModal(modal);
                    }
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') {
                    return;
                }

                document.querySelectorAll('.pkm-modal').forEach((modal) => {
                    if (!modal.hidden) {
                        closeModal(modal);
                    }
                });
            });

            if (modalToReopen) {
                openModal(document.getElementById(modalToReopen));
            }
        });
    </script>
@endpush
