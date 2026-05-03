@php
    $tanggalValue = old('tanggal', filled($jadwal->tanggal) ? \Illuminate\Support\Carbon::parse($jadwal->tanggal)->format('Y-m-d') : '');
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
        ->groupBy(fn ($kegiatan) => str_contains(mb_strtolower($kegiatan->nama_kegiatan), 'kluster')
            || str_contains(mb_strtolower($kegiatan->nama_kegiatan), 'klaster')
            ? 'Poli Layanan'
            : 'Layanan Lainnya');
@endphp

@push('styles')
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

        .pkm-helper-note {
            margin-top: 10px;
            color: var(--pkm-text-muted);
            font-size: 0.92rem;
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
    data-ignore-jadwal="{{ $jadwal->exists ? $jadwal->id : '' }}"
>
    <div class="pkm-planning-stack">
        <div class="pkm-form-grid">
            <div class="pkm-field">
                <label for="kegiatan_id">Layanan atau Poli</label>
                <select id="kegiatan_id" class="pkm-input" name="kegiatan_id" required>
                    <option value="">Pilih layanan</option>
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
                <input id="waktu_mulai" class="pkm-input" type="time" name="waktu_mulai" value="{{ old('waktu_mulai', $jadwal->waktu_mulai?->format('H:i')) }}">
            </div>

            <div class="pkm-field">
                <label for="waktu_selesai">Waktu selesai</label>
                <input id="waktu_selesai" class="pkm-input" type="time" name="waktu_selesai" value="{{ old('waktu_selesai', $jadwal->waktu_selesai?->format('H:i')) }}">
            </div>

            <div class="pkm-field pkm-field--full">
                <label for="keterangan">Keterangan</label>
                <textarea id="keterangan" class="pkm-input" name="keterangan" rows="4">{{ old('keterangan', $jadwal->keterangan) }}</textarea>
            </div>
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
            </div>

            <div class="pkm-assignment-list" id="assignment-list">
                @foreach ($petugasRows->values() as $index => $petugas)
                    @php
                        $selectedAvailability = $availabilityMap[(int) ($petugas['pegawai_id'] ?? 0)] ?? null;
                    @endphp
                    <div class="pkm-assignment-row" data-assignment-row>
                        <div class="pkm-assignment-row__head">
                            <strong>Petugas {{ $index + 1 }}</strong>
                            <button type="button" class="pkm-danger-button" data-remove-assignment>
                                <i data-lucide="trash-2" class="size-4"></i>
                                <span>Hapus</span>
                            </button>
                        </div>

                        <div class="pkm-form-grid">
                            <div class="pkm-field">
                                <label>Pegawai</label>
                                <select class="pkm-input" name="petugas[{{ $index }}][pegawai_id]" data-pegawai-select required>
                                    <option value="">Pilih pegawai</option>
                                    @foreach ($pegawaiOptions as $pegawai)
                                        @php
                                            $optionAvailability = $availabilityMap[$pegawai->id] ?? null;
                                            $optionLabel = $pegawai->nama.' - '.$pegawai->jabatan;
                                            if ($optionAvailability) {
                                                $optionLabel .= ' - Tidak tersedia: '.$optionAvailability['summary'];
                                            } else {
                                                $optionLabel .= ' - Tersedia';
                                            }
                                        @endphp
                                        <option
                                            value="{{ $pegawai->id }}"
                                            data-base-label="{{ $pegawai->nama }} - {{ $pegawai->jabatan }}"
                                            @disabled($optionAvailability)
                                            @selected((string) ($petugas['pegawai_id'] ?? '') === (string) $pegawai->id)
                                        >
                                            {{ $optionLabel }}
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
                        <strong>Belum ada jadwal layanan.</strong>
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
                <i data-lucide="trash-2" class="size-4"></i>
                <span>Hapus</span>
            </button>
        </div>

        <div class="pkm-form-grid">
            <div class="pkm-field">
                <label>Pegawai</label>
                <select class="pkm-input" data-assignment-name="pegawai_id" data-pegawai-select required>
                    <option value="">Pilih pegawai</option>
                    @foreach ($pegawaiOptions as $pegawai)
                        <option value="{{ $pegawai->id }}" data-base-label="{{ $pegawai->nama }} - {{ $pegawai->jabatan }}">
                            {{ $pegawai->nama }} - {{ $pegawai->jabatan }}
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

@push('scripts')
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

            const refreshRows = () => {
                list.querySelectorAll('[data-assignment-row]').forEach((row, index) => {
                    const title = row.querySelector('[data-assignment-title]') || row.querySelector('.pkm-assignment-row__head strong');

                    if (title) {
                        title.textContent = `Petugas ${index + 1}`;
                    }

                    row.querySelectorAll('[data-assignment-name], select[name^="petugas["], input[name^="petugas["]]').forEach((input) => {
                        const field = input.dataset.assignmentName || input.name.match(/\]\[(.+)\]$/)?.[1];

                        if (field) {
                            input.name = `petugas[${index}][${field}]`;
                        }
                    });
                });
            };

            const bindRemoveButton = (scope) => {
                scope.querySelectorAll('[data-remove-assignment]').forEach((button) => {
                    button.onclick = function () {
                        const rows = list.querySelectorAll('[data-assignment-row]');

                        if (rows.length === 1) {
                            return;
                        }

                        this.closest('[data-assignment-row]')?.remove();
                        refreshRows();
                        refreshAssignmentNotes();
                    };
                });
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
                planner.querySelectorAll('[data-pegawai-select]').forEach((select) => {
                    select.querySelectorAll('option[value]').forEach((option) => {
                        const baseLabel = option.dataset.baseLabel || option.textContent.trim();
                        const availability = availabilityMap[option.value];

                        option.disabled = Boolean(availability);
                        option.textContent = availability
                            ? `${baseLabel} - Tidak tersedia: ${availability.summary}`
                            : `${baseLabel} - Tersedia`;
                    });

                    if (select.value && availabilityMap[select.value]) {
                        select.value = '';
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
                        const details = Array.isArray(availability.details) && availability.details.length
                            ? ` (${availability.details.join(' | ')})`
                            : '';

                        note.className = 'pkm-assignment-row__availability is-warning';
                        note.textContent = `Tidak tersedia: ${availability.summary}${details}`;

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
                        : renderEmptyState('Tidak ada dinas luar disetujui.', 'Tanggal ini masih longgar untuk menyusun jadwal layanan.');
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
                        : renderEmptyState('Belum ada jadwal layanan.', 'PJ bisa mulai menyusun layanan pada tanggal ini.');
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
                bindRemoveButton(list);
                updateSelectOptionLabels();
                refreshAssignmentNotes();
            });

            planner.addEventListener('change', function (event) {
                if (event.target.matches('[data-pegawai-select]')) {
                    refreshAssignmentNotes();
                }
            });

            dateInput.addEventListener('change', fetchPlanningContext);

            refreshRows();
            bindRemoveButton(list);
            updateSelectOptionLabels();
            refreshAssignmentNotes();
        });
    </script>
@endpush
