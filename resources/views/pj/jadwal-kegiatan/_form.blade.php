@php
    $tanggalValue = old('tanggal', filled($jadwal->tanggal) ? \Illuminate\Support\Carbon::parse($jadwal->tanggal)->format('Y-m-d') : '');
    $waktuMulaiValue = old('waktu_mulai', $jadwal->waktu_mulai?->format('H:i'));
    $waktuSelesaiValue = old('waktu_selesai', $jadwal->waktu_selesai?->format('H:i'));
    $oldPetugas = old('petugas');
    $petugasRows = collect($oldPetugas ?: ($jadwal->jadwalPegawai ?? collect())->map(fn ($item) => [
        'pegawai_id' => $item->pegawai_id,
        'peran_tugas' => $item->peran_tugas,
        'status_penugasan' => $item->status_penugasan,
    ])->all());
    $availabilityMap = $planningContext['availability_map'] ?? [];

    if ($petugasRows->isEmpty()) {
        $petugasRows = collect([[
            'pegawai_id' => '',
            'peran_tugas' => '',
            'status_penugasan' => 'dijadwalkan',
        ]]);
    }

    $groupedKegiatanOptions = collect($kegiatanOptions)
        ->groupBy(function ($kegiatan) {
            if (($kegiatan->jenis ?? 'layanan') === 'dinas_luar') {
                return 'Dinas Luar';
            }

            return str_contains(mb_strtolower($kegiatan->nama_kegiatan), 'kluster')
                || str_contains(mb_strtolower($kegiatan->nama_kegiatan), 'klaster')
                ? 'Poli Layanan'
                : 'Layanan Lainnya';
        });
    $timeOptions = collect(range(0, 47))
        ->map(function (int $slot) {
            $hour = str_pad((string) intdiv($slot, 2), 2, '0', STR_PAD_LEFT);
            $minute = $slot % 2 === 0 ? '00' : '30';

            return $hour.':'.$minute;
        })
        ->push('23:59');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('template/dist/css/vendors/tom-select.css') }}">
    <style>
        .pkm-planning-grid {
            display: grid;
            grid-template-columns: 1.25fr 0.95fr;
            gap: 24px;
            align-items: start;
        }

        .pkm-planning-stack {
            display: grid;
            gap: 16px;
        }

        .pkm-planning-metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .pkm-planning-metric {
            padding: 16px 18px;
            border: 1px solid var(--pkm-border);
            border-radius: 20px;
            background: #fbfdff;
        }

        .pkm-planning-metric strong {
            display: block;
            font-size: 1.75rem;
            line-height: 1;
            color: var(--pkm-primary);
        }

        .pkm-planning-metric span {
            display: block;
            margin-top: 8px;
            color: var(--pkm-text-muted);
            font-size: 0.92rem;
        }

        .pkm-planning-list {
            display: grid;
            gap: 12px;
        }

        .pkm-planning-item {
            padding: 16px 18px;
            border: 1px solid var(--pkm-border);
            border-radius: 18px;
            background: #fbfdff;
        }

        .pkm-planning-item strong,
        .pkm-planning-item span {
            display: block;
        }

        .pkm-planning-item span {
            color: var(--pkm-text-muted);
            margin-top: 4px;
        }

        .pkm-assignment-list {
            display: grid;
            gap: 16px;
        }

        .pkm-assignment-row {
            padding: 18px;
            border: 1px solid var(--pkm-border);
            border-radius: 20px;
            background: #fbfdff;
        }

        .pkm-assignment-row__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .pkm-assignment-row__availability {
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: 16px;
            background: #eff6ff;
            color: #244a7c;
            font-size: 0.92rem;
            display: none;
        }

        .pkm-assignment-row__availability.is-warning {
            display: block;
            background: #fff3e0;
            color: #9a6700;
        }

        .pkm-assignment-row__availability.is-ok {
            display: block;
            background: #eefbf2;
            color: #1f7a46;
        }

        .pkm-modal[hidden] {
            display: none;
        }

        .pkm-modal {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: grid;
            place-items: center;
        }

        .pkm-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
        }

        .pkm-modal__panel {
            position: relative;
            z-index: 1;
            width: min(92vw, 560px);
            border-radius: 24px;
            background: #fff;
            border: 1px solid var(--pkm-border);
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.2);
            padding: 22px;
        }

        .pkm-modal__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .pkm-modal__actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .pkm-helper-note {
            margin-top: 10px;
            color: var(--pkm-text-muted);
            font-size: 0.92rem;
        }

        .pkm-planning-grid .pkm-input {
            padding-left: 1.45rem;
        }

        .pkm-planning-grid [data-pegawai-select].pkm-input {
            padding-left: 0;
        }

        .ts-wrapper {
            width: 100%;
        }

        .ts-wrapper.single .ts-control,
        .ts-wrapper.single.input-active .ts-control {
            border-radius: 1.15rem;
            border: 1px solid rgba(93, 143, 112, 0.18);
            background: rgba(246, 251, 248, 0.9);
            height: 56px;
            min-height: 56px;
            padding: 0 2.75rem 0 1.45rem;
            box-shadow: none;
            width: 100%;
            display: flex;
            align-items: center;
        }

        .ts-wrapper.single .ts-control > input {
            font-size: 15px;
            min-width: 100% !important;
            width: 100% !important;
        }

        .ts-wrapper.focus .ts-control {
            border-color: rgba(77, 143, 106, 0.45);
            box-shadow: 0 0 0 4px rgba(77, 143, 106, 0.12);
        }

        .ts-wrapper.single .ts-control .item,
        .ts-wrapper.single .ts-control .placeholder {
            width: 100%;
        }

        .ts-dropdown {
            border-radius: 1rem;
            border: 1px solid var(--pkm-border);
            box-shadow: 0 18px 40px rgba(58, 78, 113, 0.08);
        }

        .ts-dropdown .option,
        .ts-dropdown .create {
            padding: 0.8rem 1rem;
        }

        .ts-dropdown .active {
            background: var(--pkm-primary-pale);
            color: var(--pkm-primary-strong);
        }

        .ts-dropdown .option.is-disabled,
        .ts-dropdown .option[data-disabled="true"] {
            opacity: 1;
            cursor: not-allowed;
            background: #f3f4f6;
            color: #9ca3af;
            pointer-events: none;
            user-select: none;
        }

        @media (max-width: 1199px) {
            .pkm-planning-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .pkm-planning-metrics {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

<div
    class="pkm-planning-grid"
    data-planning-form
    data-availability-url="{{ route('pj.jadwal-kegiatan.availability') }}"
    data-release-conflict-url="{{ route('pj.jadwal-kegiatan.release-from-conflict') }}"
    data-ignore-jadwal="{{ $jadwal->exists ? $jadwal->id : '' }}"
>
    <div class="pkm-planning-stack">
        <div class="pkm-form-grid">
            <div class="pkm-field">
                <label for="kegiatan_id">Daftar Kegiatan</label>
                <select id="kegiatan_id" class="pkm-input" name="kegiatan_id" required>
                    <option value="">Pilih kegiatan</option>
                    @foreach ($groupedKegiatanOptions as $groupLabel => $items)
                        <optgroup label="{{ $groupLabel }}">
                            @foreach ($items as $kegiatan)
                                <option value="{{ $kegiatan->id }}" @selected((string) old('kegiatan_id', $jadwal->kegiatan_id) === (string) $kegiatan->id)>
                                    {{ $kegiatan->nama_kegiatan }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <div class="pkm-field">
                <label for="status">Status jadwal</label>
                <select id="status" class="pkm-input" name="status" required>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $jadwal->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pkm-field">
                <label for="tanggal">Tanggal layanan</label>
                <input id="tanggal" class="pkm-input" type="date" name="tanggal" value="{{ $tanggalValue }}" required>
            </div>

            <div class="pkm-field">
                <label for="lokasi">Lokasi</label>
                <input id="lokasi" class="pkm-input" type="text" name="lokasi" value="{{ old('lokasi', $jadwal->lokasi) }}" maxlength="200" required>
            </div>

            <div class="pkm-field">
                <label for="waktu_mulai">Waktu mulai</label>
                <select id="waktu_mulai" class="pkm-input" name="waktu_mulai">
                    <option value="">Pilih waktu mulai</option>
                    @foreach ($timeOptions as $timeOption)
                        <option value="{{ $timeOption }}" @selected($waktuMulaiValue === $timeOption)>{{ $timeOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pkm-field">
                <label for="waktu_selesai">Waktu selesai</label>
                <select id="waktu_selesai" class="pkm-input" name="waktu_selesai">
                    <option value="">Pilih waktu selesai</option>
                    @foreach ($timeOptions as $timeOption)
                        <option value="{{ $timeOption }}" @selected($waktuSelesaiValue === $timeOption)>{{ $timeOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pkm-field pkm-field--full">
                <label for="keterangan">Keterangan</label>
                <textarea id="keterangan" class="pkm-input" name="keterangan" rows="4">{{ old('keterangan', $jadwal->keterangan) }}</textarea>
            </div>

            <input type="hidden" name="stay_on_edit" value="{{ $jadwal->exists ? '1' : '0' }}">
        </div>

        <section class="pkm-card">
            <div class="pkm-card__head">
                <div>
                    <h3 style="font-weight: bold">Penugasan Pegawai</h3>
                    <br>
                </div>
                <button type="button" class="pkm-secondary-button" id="add-assignment-row">
                    <i data-lucide="plus" class="size-4"></i>
                    <span>Tambah Petugas</span>
                </button>
            </div><br>

            <div class="pkm-assignment-list" id="assignment-list">
                @foreach ($petugasRows->values() as $index => $petugas)
                    @php
                        $selectedAvailability = $availabilityMap[(int) ($petugas['pegawai_id'] ?? 0)] ?? null;
                    @endphp
                    <div class="pkm-assignment-row" data-assignment-row>
                        <div class="pkm-assignment-row__head">
                            <strong data-assignment-title>Petugas {{ $index + 1 }}</strong>
                            <button type="button" class="pkm-danger-button" data-remove-assignment>
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 6h18"></path>
                                    <path d="M8 6V4h8v2"></path>
                                    <path d="M19 6l-1 14H6L5 6"></path>
                                    <path d="M10 11v6"></path>
                                    <path d="M14 11v6"></path>
                                </svg>
                                <span>Hapus</span>
                            </button>
                        </div>

                        <div class="pkm-form-grid">
                            <div class="pkm-field">
                                <label>Pegawai</label>
                                <select class="" name="petugas[{{ $index }}][pegawai_id]" data-pegawai-select required>
                                    <option value="">Pilih pegawai</option>
                                    @foreach ($pegawaiOptions as $pegawai)
                                        <option
                                            value="{{ $pegawai->id }}"
                                            data-base-label="{{ $pegawai->nama }}"
                                            @selected((string) ($petugas['pegawai_id'] ?? '') === (string) $pegawai->id)
                                        >
                                            {{ $pegawai->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="pkm-field">
                                <label>Status penugasan</label>
                                <select class="pkm-input" name="petugas[{{ $index }}][status_penugasan]" required>
                                    @foreach ($statusPenugasanOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(($petugas['status_penugasan'] ?? 'dijadwalkan') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="pkm-field pkm-field--full">
                                <label>Peran tugas</label>
                                <input class="pkm-input" type="text" name="petugas[{{ $index }}][peran_tugas]" value="{{ $petugas['peran_tugas'] ?? '' }}" maxlength="100" placeholder="Contoh: Penanggung jawab poli, Asisten layanan, Dokumentasi">
                            </div>
                        </div>

                        <p
                            class="pkm-assignment-row__availability {{ $selectedAvailability ? 'is-warning' : 'is-ok' }}"
                            data-availability-note
                        >
                            {{ $selectedAvailability ? 'Tidak tersedia: '.$selectedAvailability['summary'] : 'Pegawai ini tersedia pada tanggal yang dipilih.' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="pkm-planning-stack">
        <section class="pkm-card">
            <div class="pkm-card__head">
                <div>
                    <h3 style="font-weight:bold">Referensi Penjadwalan</h3>
                    <br>
                </div>
                <span class="pkm-pill is-blue" data-planning-date>{{ $planningContext['selected_date_label'] }}</span>
            </div>

            <div class="pkm-planning-metrics">
                <article class="pkm-planning-metric">
                    <strong data-available-count>{{ $planningContext['availability_summary']['available_count'] }}</strong>
                    <span>Pegawai tersedia</span>
                </article>
                <article class="pkm-planning-metric">
                    <strong data-unavailable-count>{{ $planningContext['availability_summary']['unavailable_count'] }}</strong>
                    <span>Pegawai tidak tersedia</span>
                </article>
                <article class="pkm-planning-metric">
                    <strong data-approved-dinas-count>{{ count($planningContext['approved_dinas']) }}</strong>
                    <span>Dinas luar disetujui</span>
                </article>
            </div>
        </section>

        <section class="pkm-card">
            <div class="pkm-card__head">
                <div>
                    <h3 style="font-weight:bold">Dinas Luar Disetujui</h3>
                    <br>
                </div>
            </div>

            <div class="pkm-planning-list" data-approved-dinas-list>
                @forelse ($planningContext['approved_dinas'] as $item)
                    <article class="pkm-planning-item">
                        <strong>{{ $item['pegawai'] }} - {{ $item['kegiatan'] }}</strong>
                        <span>{{ $item['jabatan'] }} - {{ $item['tujuan'] }}</span>
                        <span>{{ $item['range_label'] }}</span>
                    </article>
                @empty
                    <div class="pkm-empty-state" data-approved-dinas-empty>
                        <strong>Tidak ada dinas luar disetujui.</strong>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="pkm-card">
            <div class="pkm-card__head">
                <div>
                    <h3 style="font-weight: bold">Jadwal yang Sudah Ada</h3>
                    <br>
                </div>
            </div>

            <div class="pkm-planning-list" data-existing-schedules-list>
                @forelse ($planningContext['existing_schedules'] as $item)
                    <article class="pkm-planning-item">
                        <strong>{{ $item['title'] }}</strong>
                        <span>{{ $item['time_label'] }} - {{ $item['lokasi'] }}</span>
                        <span>{{ $item['pegawai'] }}</span>
                    </article>
                @empty
                    <div class="pkm-empty-state" data-existing-schedules-empty>
                        <strong>Belum ada jadwal kegiatan.</strong>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>

<div class="pkm-form-actions">
    <a href="{{ route('pj.jadwal-kegiatan.index') }}" class="pkm-secondary-button">
        <i data-lucide="arrow-left" class="size-4"></i>
        <span>Kembali</span>
    </a>
    @if (! $jadwal->exists)
        <button type="submit" class="pkm-secondary-button" name="save_action" value="save_and_create">
            <i data-lucide="plus" class="size-4"></i>
            <span>Simpan dan Buat Lagi</span>
        </button>
    @endif
    <button type="submit" class="pkm-primary-button">
        <i data-lucide="save" class="size-4"></i>
        <span>{{ $submitLabel }}</span>
    </button>
</div>

<template id="assignment-template">
    <div class="pkm-assignment-row" data-assignment-row>
        <div class="pkm-assignment-row__head">
            <strong data-assignment-title>Petugas</strong>
            <button type="button" class="pkm-danger-button" data-remove-assignment>
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 6h18"></path>
                    <path d="M8 6V4h8v2"></path>
                    <path d="M19 6l-1 14H6L5 6"></path>
                    <path d="M10 11v6"></path>
                    <path d="M14 11v6"></path>
                </svg>
                <span>Hapus</span>
            </button>
        </div>

        <div class="pkm-form-grid">
            <div class="pkm-field">
                <label>Pegawai</label>
                <select class="pkm-input" data-assignment-name="pegawai_id" data-pegawai-select required>
                    <option value="">Pilih pegawai</option>
                    @foreach ($pegawaiOptions as $pegawai)
                        <option value="{{ $pegawai->id }}" data-base-label="{{ $pegawai->nama }}">
                            {{ $pegawai->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pkm-field">
                <label>Status penugasan</label>
                <select class="pkm-input" data-assignment-name="status_penugasan" required>
                    @foreach ($statusPenugasanOptions as $value => $label)
                        <option value="{{ $value }}" @selected($value === 'dijadwalkan')>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pkm-field pkm-field--full">
                <label>Peran tugas</label>
                <input class="pkm-input" type="text" data-assignment-name="peran_tugas" maxlength="100" placeholder="Contoh: Penanggung jawab poli, Asisten layanan, Dokumentasi">
            </div>
        </div>

        <p class="pkm-assignment-row__availability is-ok" data-availability-note>Pegawai ini tersedia pada tanggal yang dipilih.</p>
    </div>
</template>

<div class="pkm-modal" data-conflict-modal hidden>
    <div class="pkm-modal__backdrop" data-conflict-close></div>
    <div class="pkm-modal__panel" role="dialog" aria-modal="true" aria-labelledby="conflict-modal-title">
        <div class="pkm-modal__head">
            <h3 id="conflict-modal-title" style="font-weight:700;">Konfirmasi ubah jadwal</h3>
            <button type="button" class="pkm-icon-button" data-conflict-close aria-label="Tutup">×</button>
        </div>
        <p data-conflict-modal-text style="margin:12px 0 0; color:var(--pkm-text-muted);"></p>
        <div class="pkm-modal__actions">
            <button type="button" class="pkm-secondary-button" data-conflict-close>Batal</button>
            <button type="button" class="pkm-primary-button" data-conflict-confirm>Ya, ubah jadwal</button>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('template/dist/js/vendors/tom-select.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const planner = document.querySelector('[data-planning-form]');
            const list = document.getElementById('assignment-list');
            const addButton = document.getElementById('add-assignment-row');
            const template = document.getElementById('assignment-template');
            const dateInput = document.getElementById('tanggal');

            if (!planner || !list || !addButton || !template || !dateInput) {
                return;
            }

            let availabilityMap = @json($planningContext['availability_map'] ?? []);

            const planningDate = planner.querySelector('[data-planning-date]');
            const availableCount = planner.querySelector('[data-available-count]');
            const unavailableCount = planner.querySelector('[data-unavailable-count]');
            const approvedDinasCount = planner.querySelector('[data-approved-dinas-count]');
            const approvedDinasList = planner.querySelector('[data-approved-dinas-list]');
            const existingSchedulesList = planner.querySelector('[data-existing-schedules-list]');
            const conflictModal = document.querySelector('[data-conflict-modal]');
            const conflictModalText = document.querySelector('[data-conflict-modal-text]');
            const conflictConfirmButton = document.querySelector('[data-conflict-confirm]');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let pendingConflict = null;

            const renderIcons = () => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            };

            const closeConflictModal = () => {
                if (!conflictModal) {
                    return;
                }

                conflictModal.hidden = true;
                pendingConflict = null;
            };

            const openConflictModal = (payload) => {
                if (!conflictModal || !conflictModalText) {
                    return;
                }

                pendingConflict = payload;
                conflictModalText.innerHTML = `
                    Pegawai <strong>${escapeHtml(payload.pegawaiName)}</strong> sedang memiliki jadwal bentrok:
                    <br><strong>${escapeHtml(payload.title)}</strong>
                    <br>${escapeHtml(payload.timeLabel)} - ${escapeHtml(payload.lokasi)}
                    <br><br>Jika dilanjutkan, pegawai ini akan dilepas dari jadwal bentrok tersebut agar bisa dipakai pada jadwal yang sedang disusun.
                `;
                conflictModal.hidden = false;
            };

            const releaseConflict = async () => {
                if (!pendingConflict) {
                    return;
                }

                try {
                    const response = await fetch(planner.dataset.releaseConflictUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: new URLSearchParams({
                            jadwal_id: String(pendingConflict.jadwalId),
                            pegawai_id: String(pendingConflict.pegawaiId),
                        }),
                    });

                    if (!response.ok) {
                        return;
                    }

                    const form = planner.closest('form');
                    closeConflictModal();
                    await fetchPlanningContext();
                    const stayOnEditInput = form?.querySelector('input[name="stay_on_edit"]');
                    if (stayOnEditInput) {
                        stayOnEditInput.value = '1';
                    }
                    form?.requestSubmit();
                } catch (error) {
                    console.error(error);
                }
            };

            const initializePegawaiSearch = (scope = planner) => {
                if (typeof window.TomSelect !== 'function') {
                    return;
                }

                scope.querySelectorAll('[data-pegawai-select]').forEach((select) => {
                    if (select.tomselect) {
                        select.tomselect.sync();
                        select.tomselect.refreshOptions(false);
                        return;
                    }

                    new window.TomSelect(select, {
                        create: false,
                        persist: false,
                        maxOptions: 200,
                        allowEmptyOption: true,
                        closeAfterSelect: true,
                        searchField: ['text'],
                        sortField: [
                            { field: 'text', direction: 'asc' },
                        ],
                        disabledField: 'disabled',
                        placeholder: 'Cari atau pilih pegawai',
                        render: {
                            option(data, escape) {
                                return `<div class="option">${escape(data.text)}</div>`;
                            },
                        },
                        onChange() {
                            updateSelectOptionLabels();
                            refreshAssignmentNotes();
                        },
                    });
                });
            };

            const refreshRows = () => {
                const rows = list.querySelectorAll('[data-assignment-row]');

                rows.forEach((row, index) => {
                    const title = row.querySelector('[data-assignment-title]');
                    const removeButton = row.querySelector('[data-remove-assignment]');

                    if (title) {
                        title.textContent = `Petugas ${index + 1}`;
                    }

                    if (removeButton) {
                        removeButton.disabled = rows.length === 1;
                    }

                    row.querySelectorAll('select, input, textarea').forEach((input) => {
                        const field = input.dataset.assignmentName || input.name?.match(/\]\[(.+)\]$/)?.[1];

                        if (field) {
                            input.name = `petugas[${index}][${field}]`;
                        }
                    });
                });

                initializePegawaiSearch(list);
                renderIcons();
            };

            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const renderEmptyState = (title, description) => `
                <div class="pkm-empty-state">
                    <strong>${escapeHtml(title)}</strong>
                    <p>${escapeHtml(description)}</p>
                </div>
            `;

            const updateSelectOptionLabels = () => {
                const selects = Array.from(planner.querySelectorAll('[data-pegawai-select]'));
                const selectedValues = selects
                    .map((select) => select.value)
                    .filter(Boolean);

                selects.forEach((select) => {
                    select.querySelectorAll('option[value]').forEach((option) => {
                        const baseLabel = option.dataset.baseLabel || option.textContent.trim();
                        const availability = availabilityMap[option.value];
                        const selectedInAnotherRow = selectedValues.includes(option.value) && select.value !== option.value;

                        option.disabled = false;
                        option.dataset.disabled = 'false';
                        option.textContent = baseLabel;

                        if (selectedInAnotherRow) {
                            option.textContent = `${baseLabel} (sudah dipakai di petugas lain)`;
                        } else if (availability) {
                            option.textContent = `${baseLabel} (punya jadwal lain)`;
                        }
                    });

                    if (select.tomselect) {
                        Object.values(select.tomselect.options).forEach((item) => {
                            item.disabled = false;
                        });
                        select.tomselect.sync();
                        select.tomselect.refreshOptions(false);
                    }
                });
            };

            const refreshAssignmentNotes = () => {
                planner.querySelectorAll('[data-assignment-row]').forEach((row) => {
                    const select = row.querySelector('[data-pegawai-select]');
                    const note = row.querySelector('[data-availability-note]');
                    const availability = select ? availabilityMap[select.value] : null;

                    if (!note) {
                        return;
                    }

                    if (!select || !select.value) {
                        note.className = 'pkm-assignment-row__availability';
                        note.textContent = '';
                        return;
                    }

                    if (availability) {
                        note.className = 'pkm-assignment-row__availability is-warning';
                        const detailLines = Array.isArray(availability.details) ? availability.details : [];
                        const conflict = Array.isArray(availability.conflicts) ? availability.conflicts[0] : null;
                        const detailHtml = detailLines.length
                            ? `<div style="margin-top:8px; display:grid; gap:4px;">${detailLines.map((line) => `<div>- ${escapeHtml(line)}</div>`).join('')}</div>`
                            : '';
                        const actionHtml = conflict
                            ? `<div style="margin-top:12px;"><button type="button" class="pkm-primary-button" data-release-conflict data-conflict-jadwal-id="${escapeHtml(conflict.jadwal_id)}" data-conflict-pegawai-id="${escapeHtml(select.value)}" data-conflict-pegawai-name="${escapeHtml(availability.name || 'Pegawai')}">Ubah Jadwal</button></div>`
                            : '';

                        note.innerHTML = `
                            <strong>Pegawai ini sudah memiliki jadwal pada ${escapeHtml(availability.date_label || '-')}</strong><br>
                            ${escapeHtml(availability.summary || 'Konflik jadwal terdeteksi.')}
                            ${detailHtml}
                            ${actionHtml}
                        `;

                        return;
                    }

                    note.className = 'pkm-assignment-row__availability is-ok';
                    note.textContent = 'Pegawai ini tersedia pada tanggal yang dipilih.';
                });
            };

            const renderPlanningContext = (context) => {
                availabilityMap = context.availability_map || {};

                if (planningDate) {
                    planningDate.textContent = context.selected_date_label || '-';
                }

                if (availableCount) {
                    availableCount.textContent = context.availability_summary?.available_count ?? '0';
                }

                if (unavailableCount) {
                    unavailableCount.textContent = context.availability_summary?.unavailable_count ?? '0';
                }

                if (approvedDinasCount) {
                    approvedDinasCount.textContent = Array.isArray(context.approved_dinas) ? context.approved_dinas.length : '0';
                }

                if (approvedDinasList) {
                    approvedDinasList.innerHTML = Array.isArray(context.approved_dinas) && context.approved_dinas.length
                        ? context.approved_dinas.map((item) => `
                            <article class="pkm-planning-item">
                                <strong>${escapeHtml(item.pegawai)} - ${escapeHtml(item.kegiatan)}</strong>
                                <span>${escapeHtml(item.jabatan)} - ${escapeHtml(item.tujuan)}</span>
                                <span>${escapeHtml(item.range_label)}</span>
                            </article>
                        `).join('')
                        : renderEmptyState('Tidak ada dinas luar disetujui.', 'Tanggal ini masih longgar untuk menyusun jadwal kegiatan.');
                }

                if (existingSchedulesList) {
                    existingSchedulesList.innerHTML = Array.isArray(context.existing_schedules) && context.existing_schedules.length
                        ? context.existing_schedules.map((item) => `
                            <article class="pkm-planning-item">
                                <strong>${escapeHtml(item.title)}</strong>
                                <span>${escapeHtml(item.time_label)} - ${escapeHtml(item.lokasi)}</span>
                                <span>${escapeHtml(item.pegawai)}</span>
                            </article>
                        `).join('')
                        : renderEmptyState('Belum ada jadwal kegiatan.', 'PJ bisa mulai menyusun kegiatan pada tanggal ini.');
                }

                updateSelectOptionLabels();
                refreshAssignmentNotes();
            };

            const fetchPlanningContext = async () => {
                if (!dateInput.value) {
                    return;
                }

                const endpoint = new URL(planner.dataset.availabilityUrl, window.location.origin);
                endpoint.searchParams.set('date', dateInput.value);

                if (planner.dataset.ignoreJadwal) {
                    endpoint.searchParams.set('ignore_jadwal', planner.dataset.ignoreJadwal);
                }

                try {
                    const response = await fetch(endpoint.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    renderPlanningContext(await response.json());
                } catch (error) {
                    console.error(error);
                }
            };

            addButton.addEventListener('click', function () {
                const fragment = template.content.cloneNode(true);
                list.appendChild(fragment);
                refreshRows();
                updateSelectOptionLabels();
                refreshAssignmentNotes();
            });

            list.addEventListener('click', function (event) {
                const removeButton = event.target.closest('[data-remove-assignment]');
                const releaseButton = event.target.closest('[data-release-conflict]');

                if (!removeButton) {
                    if (!releaseButton) {
                        return;
                    }

                    const row = releaseButton.closest('[data-assignment-row]');
                    const select = row?.querySelector('[data-pegawai-select]');
                    const note = row?.querySelector('[data-availability-note]');
                    const availability = select ? availabilityMap[select.value] : null;
                    const conflict = availability && Array.isArray(availability.conflicts) ? availability.conflicts[0] : null;

                    if (!select || !conflict) {
                        return;
                    }

                    openConflictModal({
                        jadwalId: conflict.jadwal_id,
                        pegawaiId: select.value,
                        pegawaiName: releaseButton.dataset.conflictPegawaiName || availability.name || 'Pegawai',
                        title: conflict.title || 'Kegiatan',
                        timeLabel: conflict.time_label || '-',
                        lokasi: conflict.lokasi || '-',
                        note,
                    });

                    return;
                }

                const rows = list.querySelectorAll('[data-assignment-row]');

                if (rows.length === 1) {
                    return;
                }

                removeButton.closest('[data-assignment-row]')?.remove();
                refreshRows();
                refreshAssignmentNotes();
            });

            planner.addEventListener('change', function (event) {
                if (event.target.matches('[data-pegawai-select]')) {
                    refreshAssignmentNotes();
                }
            });

            document.querySelectorAll('[data-conflict-close]').forEach((button) => {
                button.addEventListener('click', closeConflictModal);
            });

            conflictConfirmButton?.addEventListener('click', releaseConflict);

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeConflictModal();
                }
            });

            dateInput.addEventListener('change', fetchPlanningContext);

            refreshRows();
            updateSelectOptionLabels();
            refreshAssignmentNotes();
            initializePegawaiSearch();
            renderIcons();
        });
    </script>
@endpush
