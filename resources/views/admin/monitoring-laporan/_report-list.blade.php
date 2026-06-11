@if ($reports->isEmpty())
    <div class="pkm-empty-state">
        <strong>Belum ada laporan kegiatan pada filter ini.</strong>
    </div>
@else
    <div class="pkm-table-scroll">
        <div
            class="pkm-table pkm-table--laporan pkm-table--monitoring-laporan pkm-table--scrollable"
            style="--pkm-table-min-width: 1180px; --pkm-scroll-columns: minmax(280px, 1.55fr) minmax(220px, 0.9fr) minmax(220px, 0.82fr) minmax(220px, 0.85fr) minmax(180px, 0.68fr);"
        >
            <div class="pkm-table__head">
                <span>Laporan</span>
                <span>Pelaksana</span>
                <span>Referensi</span>
                <span>Waktu Dibuat</span>
                <span>Aksi</span>
            </div>

            @foreach ($reports as $report)
                <div class="pkm-table__row">
                    <div data-label="Laporan">
                        <strong>{{ $report->kegiatan_nama }}</strong>
                        <small>{{ $report->jenis_kegiatan_label }} - {{ $report->tanggal->translatedFormat('d F Y') }} - {{ $report->lokasi_kegiatan }}</small>
                    </div>
                    <div data-label="Pelaksana">
                        <strong>{{ $report->pegawai?->nama ?? 'Pegawai tidak ditemukan' }}</strong>
                        <small>{{ $report->pegawai?->jabatan ?? 'Jabatan tidak tersedia' }}</small>
                    </div>
                    <div data-label="Referensi">
                        <strong>{{ $report->waktu_kegiatan }}</strong>
                        <small>Status referensi: {{ $report->status_referensi }}</small>
                    </div>
                    <div data-label="Waktu Dibuat">
                        <strong>{{ $report->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</strong>
                        <small>Terakhir diperbarui: {{ $report->updated_at?->translatedFormat('d M Y H:i') ?? '-' }}</small>
                    </div>
                    <div data-label="Aksi">
                        <div class="pkm-row-actions">
                            @if ($report->dokumen_laporan_url)
                                <div>
                                    <a href="{{ $report->dokumen_laporan_url }}" target="_blank" rel="noopener noreferrer" class="pkm-text-link">
                                        <i data-lucide="file-text" class="size-4"></i>
                                        <span>Lihat</span>
                                    </a>
                                    <small>{{ $report->dokumen_laporan_nama ?? 'Dokumen laporan' }}</small>
                                </div>
                            @else
                                <div>
                                    <strong>Belum diunggah</strong>
                                    <small>Pegawai belum mengunggah file PDF.</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="pkm-pagination">
        @if ($reports->onFirstPage())
            <span class="pkm-pagination__muted" aria-hidden="true"><i data-lucide="chevron-left" class="size-4"></i></span>
        @else
            <a href="{{ $reports->previousPageUrl() }}" class="pkm-secondary-button" aria-label="Sebelumnya"><i data-lucide="chevron-left" class="size-4"></i></a>
        @endif

        <span class="pkm-pagination__page">{{ $reports->currentPage() }} / {{ $reports->lastPage() }}</span>

        @if ($reports->hasMorePages())
            <a href="{{ $reports->nextPageUrl() }}" class="pkm-secondary-button" aria-label="Berikutnya"><i data-lucide="chevron-right" class="size-4"></i></a>
        @else
            <span class="pkm-pagination__muted" aria-hidden="true"><i data-lucide="chevron-right" class="size-4"></i></span>
        @endif
    </div>
@endif
