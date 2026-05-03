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
