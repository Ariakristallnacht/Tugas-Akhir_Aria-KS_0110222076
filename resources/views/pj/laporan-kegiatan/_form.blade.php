@php
    $tanggalValue = old('tanggal', filled($report->tanggal) ? \Illuminate\Support\Carbon::parse($report->tanggal)->format('Y-m-d') : '');
@endphp

@push('styles')
    <style>
        .pkm-report-hint {
            padding: 18px 20px;
            border-radius: 20px;
            background: #f5fbff;
            border: 1px solid var(--pkm-border);
            color: var(--pkm-text-muted);
        }

        .pkm-report-hint strong,
        .pkm-report-hint span {
            display: block;
        }

        .pkm-report-hint span + span {
            margin-top: 4px;
        }

        .pkm-file-input {
            padding: 0.45rem 0.7rem;
        }

        .pkm-file-input::file-selector-button {
            margin-right: 0.95rem;
            padding: 0.7rem 1rem;
            border-right: 1px solid rgba(93, 143, 112, 0.24);
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            background: rgba(226, 239, 232, 0.9);
            color: var(--pkm-primary-strong);
            font-weight: 600;
        }
    </style>
@endpush

<div class="pkm-form-grid" data-report-form>
    <div class="pkm-field">
        <label for="jadwal_id">Jadwal kegiatan</label>
        <select id="jadwal_id" class="pkm-input" name="jadwal_id" data-jadwal-select required>
            <option value="">Pilih jadwal</option>
            @foreach ($jadwalOptions as $jadwal)
                <option
                    value="{{ $jadwal->id }}"
                    data-pegawai='@json($jadwal->pegawai->map(fn ($pegawai) => ['id' => $pegawai->id, 'nama' => $pegawai->nama, 'jabatan' => $pegawai->jabatan])->values())'
                    @selected((string) old('jadwal_id', $report->jadwal_id) === (string) $jadwal->id)
                >
                    {{ $jadwal->tanggal->translatedFormat('d M Y') }} - {{ $jadwal->kegiatan?->nama_kegiatan ?? 'Kegiatan' }} - {{ $jadwal->lokasi }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="pkm-field">
        <label for="pegawai_id">Pegawai pelaksana</label>
        <select id="pegawai_id" class="pkm-input" name="pegawai_id" data-pegawai-select required>
            <option value="">Pilih pegawai</option>
            @foreach ($pegawaiOptions as $pegawai)
                <option value="{{ $pegawai->id }}" @selected((string) old('pegawai_id', $report->pegawai_id) === (string) $pegawai->id)>{{ $pegawai->nama }} - {{ $pegawai->jabatan }}</option>
            @endforeach
        </select>
    </div>

    <div class="pkm-field">
        <label for="tanggal">Tanggal laporan</label>
        <input id="tanggal" class="pkm-input" type="date" name="tanggal" value="{{ $tanggalValue }}" required>
    </div>

    <div class="pkm-field pkm-field--full">
        <label for="laporan">Isi laporan kegiatan</label>
        <textarea id="laporan" class="pkm-input" name="laporan" rows="8" placeholder="Jelaskan hasil kegiatan, capaian layanan, hambatan, dan tindak lanjut." required>{{ old('laporan', $report->laporan) }}</textarea>
    </div>

    <div class="pkm-field pkm-field--full">
        <label for="dokumen_laporan">Dokumen laporan PDF</label>
        <input id="dokumen_laporan" class="pkm-input pkm-file-input" type="file" name="dokumen_laporan" accept=".pdf,application/pdf">
        <input type="hidden" name="dokumen_laporan_existing" value="{{ $report->dokumen_laporan_path }}">
        @if ($report->dokumen_laporan_path)
            <small style="display: block; margin-top: 8px;">
                File saat ini:
                <a href="{{ $report->dokumen_laporan_url }}" target="_blank" rel="noopener noreferrer" class="pkm-text-link" style="padding: 0; background: transparent;">
                    <i data-lucide="file-text" class="size-4"></i>
                    <span>{{ $report->dokumen_laporan_nama ?? 'Lihat dokumen laporan' }}</span>
                </a>
            </small>
        @else
            <small style="display: block; margin-top: 8px;">Unggah file PDF laporan kegiatan. Maksimal 10 MB.</small>
        @endif
        @error('dokumen_laporan')
            <small>{{ $message }}</small>
        @enderror
    </div>

    <div class="pkm-field pkm-field--full">
        <div class="pkm-report-hint" id="report-assignment-hint">
            <strong>Petugas jadwal akan muncul setelah jadwal dipilih.</strong>
            <span>PJ hanya bisa memilih pegawai yang memang terdaftar pada jadwal tersebut.</span>
        </div>
    </div>
</div>

<div class="pkm-form-actions">
    <a href="{{ route('pj.laporan-kegiatan.index') }}" class="pkm-secondary-button">
        <i data-lucide="arrow-left" class="size-4"></i>
        <span>Kembali</span>
    </a>
    <button type="submit" class="pkm-primary-button">
        <i data-lucide="save" class="size-4"></i>
        <span>{{ $submitLabel }}</span>
    </button>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('[data-report-form]');
            const jadwalSelect = document.querySelector('[data-jadwal-select]');
            const pegawaiSelect = document.querySelector('[data-pegawai-select]');
            const hint = document.getElementById('report-assignment-hint');
            const currentPegawaiId = @json((string) old('pegawai_id', $report->pegawai_id));

            if (!form || !jadwalSelect || !pegawaiSelect || !hint) {
                return;
            }

            const updatePegawaiOptions = () => {
                const selected = jadwalSelect.options[jadwalSelect.selectedIndex];
                const pegawai = selected?.dataset?.pegawai ? JSON.parse(selected.dataset.pegawai) : [];

                pegawaiSelect.innerHTML = '<option value="">Pilih pegawai</option>';

                pegawai.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `${item.nama} - ${item.jabatan}`;

                    if (String(item.id) === currentPegawaiId) {
                        option.selected = true;
                    }

                    pegawaiSelect.appendChild(option);
                });

                if (pegawai.length === 0) {
                    hint.innerHTML = '<strong>Belum ada petugas pada jadwal ini.</strong><span>Pilih jadwal lain atau lengkapi petugas di fitur jadwal kegiatan terlebih dahulu.</span>';
                    return;
                }

                hint.innerHTML = `<strong>${pegawai.length} petugas tersedia untuk laporan ini.</strong><span>${pegawai.map((item) => `${item.nama} (${item.jabatan})`).join(', ')}</span>`;
            };

            jadwalSelect.addEventListener('change', function () {
                pegawaiSelect.value = '';
                updatePegawaiOptions();
            });

            updatePegawaiOptions();
        });
    </script>
@endpush
