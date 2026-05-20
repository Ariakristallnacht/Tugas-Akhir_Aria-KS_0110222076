@php
    $tanggalValue = old('tanggal', filled($report->tanggal) ? \Illuminate\Support\Carbon::parse($report->tanggal)->format('Y-m-d') : '');
    $selectedJenis = old('jenis_kegiatan', $report->jenis_kegiatan ?: \App\Models\LaporanKegiatan::JENIS_LAYANAN);
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
        <label for="jenis_kegiatan">Jenis kegiatan</label>
        <select id="jenis_kegiatan" class="pkm-input" name="jenis_kegiatan" data-jenis-select required>
            <option value="{{ \App\Models\LaporanKegiatan::JENIS_LAYANAN }}" @selected($selectedJenis === \App\Models\LaporanKegiatan::JENIS_LAYANAN)>Layanan</option>
            <option value="{{ \App\Models\LaporanKegiatan::JENIS_DINAS_LUAR }}" @selected($selectedJenis === \App\Models\LaporanKegiatan::JENIS_DINAS_LUAR)>Dinas Luar</option>
        </select>
    </div>

    <div class="pkm-field" data-source-group="layanan">
        <label for="jadwal_id">Pilih Jadwal Layanan</label>
        <select id="jadwal_id" class="pkm-input" name="jadwal_id" data-jadwal-select>
            <option value="">Pilih jadwal layanan</option>
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

    <div class="pkm-field" data-source-group="dinas_luar">
        <label for="pengajuan_dinas_id">Pilih Pengajuan Dinas</label>
        <select id="pengajuan_dinas_id" class="pkm-input" name="pengajuan_dinas_id" data-pengajuan-select>
            <option value="">Pilih pengajuan dinas</option>
            @foreach ($pengajuanDinasOptions as $pengajuan)
                <option
                    value="{{ $pengajuan->id }}"
                    data-pegawai='@json($pengajuan->pegawai ? [['id' => $pengajuan->pegawai->id, 'nama' => $pengajuan->pegawai->nama, 'jabatan' => $pengajuan->pegawai->jabatan]] : [])'
                    @selected((string) old('pengajuan_dinas_id', $report->pengajuan_dinas_id) === (string) $pengajuan->id)
                >
                    {{ $pengajuan->tanggal_mulai->translatedFormat('d M Y') }} - {{ \Illuminate\Support\Str::limit($pengajuan->kegiatan, 60) }} - {{ $pengajuan->tujuan }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="pkm-field">
        <label for="pegawai_id">Pegawai pelaksana</label>
        @if ($lockPegawai ?? false)
            <input class="pkm-input" type="text" value="{{ $lockedPegawai?->nama ?? 'Pegawai login' }}" disabled>
            <input type="hidden" name="pegawai_id" value="{{ $lockedPegawai?->id }}">
        @else
            <select id="pegawai_id" class="pkm-input" name="pegawai_id" data-pegawai-select required>
                <option value="">Pilih pegawai</option>
                @foreach ($pegawaiOptions as $pegawai)
                    <option value="{{ $pegawai->id }}" @selected((string) old('pegawai_id', $report->pegawai_id) === (string) $pegawai->id)>{{ $pegawai->nama }} - {{ $pegawai->jabatan }}</option>
                @endforeach
            </select>
        @endif
    </div>

    <div class="pkm-field">
        <label for="tanggal">Tanggal laporan</label>
        <input id="tanggal" class="pkm-input" type="date" name="tanggal" value="{{ $tanggalValue }}" required>
    </div>

    <div class="pkm-field pkm-field--full">
        <label for="dokumen_laporan">Dokumen laporan (opsional)</label>
        <input id="dokumen_laporan" class="pkm-input pkm-file-input" type="file" name="dokumen_laporan" accept=".doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.webp">
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
            <small style="display: block; margin-top: 8px;">Unggah dokumen jika diperlukan. Format yang didukung: DOC, DOCX, XLS, XLSX, CSV, PPT, PPTX, TXT, JPG, JPEG, PNG, WEBP. Maksimal 10 MB.</small>
        @endif
        @error('dokumen_laporan')
            <small>{{ $message }}</small>
        @enderror
    </div>

    <div class="pkm-field pkm-field--full">
        <div class="pkm-report-hint" id="report-assignment-hint">
            <strong>Pilih jenis kegiatan terlebih dahulu.</strong>
            <span>Petugas akan menyesuaikan dengan jadwal layanan atau pengajuan dinas yang dipilih.</span>
        </div>
    </div>
</div>

<div class="pkm-form-actions">
    <a href="{{ route(($lockPegawai ?? false) ? 'pegawai.laporan-kegiatan.index' : 'pj.monitoring-laporan') }}" class="pkm-secondary-button">
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
            const jenisSelect = document.querySelector('[data-jenis-select]');
            const jadwalSelect = document.querySelector('[data-jadwal-select]');
            const pengajuanSelect = document.querySelector('[data-pengajuan-select]');
            const pegawaiSelect = document.querySelector('[data-pegawai-select]');
            const hint = document.getElementById('report-assignment-hint');
            const currentPegawaiId = @json((string) old('pegawai_id', $report->pegawai_id));
            const isPegawaiLocked = @json($lockPegawai ?? false);
            const sourceGroups = document.querySelectorAll('[data-source-group]');

            if (!form || !jenisSelect || !jadwalSelect || !pengajuanSelect || !hint || (!isPegawaiLocked && !pegawaiSelect)) {
                return;
            }

            const activeSourceSelect = () => jenisSelect.value === 'dinas_luar' ? pengajuanSelect : jadwalSelect;

            const updateSourceVisibility = () => {
                const activeType = jenisSelect.value;

                sourceGroups.forEach((group) => {
                    const isActive = group.dataset.sourceGroup === activeType;
                    group.hidden = !isActive;

                    const select = group.querySelector('select');

                    if (!select) {
                        return;
                    }

                    select.disabled = !isActive;

                    if (!isActive) {
                        select.value = '';
                    }
                });
            };

            const updatePegawaiOptions = () => {
                const sourceSelect = activeSourceSelect();
                const selected = sourceSelect.options[sourceSelect.selectedIndex];
                const pegawai = selected?.dataset?.pegawai ? JSON.parse(selected.dataset.pegawai) : [];

                if (!isPegawaiLocked) {
                    pegawaiSelect.innerHTML = '<option value="">Pilih pegawai</option>';
                }

                pegawai.forEach((item) => {
                    if (isPegawaiLocked) {
                        return;
                    }

                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `${item.nama} - ${item.jabatan}`;

                    if (String(item.id) === currentPegawaiId) {
                        option.selected = true;
                    }

                    pegawaiSelect.appendChild(option);
                });

                if (pegawai.length === 0) {
                    hint.innerHTML = jenisSelect.value === 'dinas_luar'
                        ? '<strong>Belum ada pegawai pada pengajuan dinas ini.</strong><span>Pilih pengajuan dinas yang valid untuk menentukan pelaksana laporan.</span>'
                        : '<strong>Belum ada petugas pada jadwal ini.</strong><span>Pilih jadwal layanan yang memiliki petugas terdaftar.</span>';
                    return;
                }

                const sourceLabel = jenisSelect.value === 'dinas_luar' ? 'pengajuan dinas' : 'jadwal layanan';
                hint.innerHTML = `<strong>${pegawai.length} petugas tersedia untuk ${sourceLabel} ini.</strong><span>${pegawai.map((item) => `${item.nama} (${item.jabatan})`).join(', ')}</span>`;
            };

            const handleSourceChange = () => {
                if (!isPegawaiLocked) {
                    pegawaiSelect.value = '';
                }

                updatePegawaiOptions();
            };

            jenisSelect.addEventListener('change', function () {
                updateSourceVisibility();
                handleSourceChange();
            });

            jadwalSelect.addEventListener('change', handleSourceChange);
            pengajuanSelect.addEventListener('change', handleSourceChange);

            updateSourceVisibility();
            updatePegawaiOptions();
        });
    </script>
@endpush
