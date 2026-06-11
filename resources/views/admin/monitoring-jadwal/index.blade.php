@extends('layouts.dashboard')

@php
    $title = 'Monitoring Jadwal Kegiatan | Puskesmas Bunar';
    $heading = 'Monitoring Jadwal Kegiatan';
    $weekdayLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
@endphp

@push('styles')
    <style>
        .pkm-dashboard-main--monitoring-jadwal {
            display: flex;
            flex-direction: column;
            gap: 24px;
            min-width: 0;
        }

        .pkm-calendar-head {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 18px;
        }

        .pkm-calendar-head__main {
            display: grid;
            gap: 14px;
            justify-items: center;
            text-align: center;
        }

        .pkm-calendar-head__title {
            display: grid;
            gap: 4px;
            justify-items: center;
        }

        .pkm-calendar-head__title p {
            margin: 0;
            color: var(--pkm-text-muted);
        }

        .pkm-calendar-head__actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .pkm-calendar-head {
                grid-template-columns: 1fr;
                justify-items: center;
            }
        }

        .pkm-modal-tabs {
            display: flex;
            gap: 10px;
            margin: 18px 0 16px;
            padding: 6px;
            border-radius: 18px;
            background: #f5f7fb;
        }

        .pkm-modal-tab {
            flex: 1;
            border: 0;
            border-radius: 14px;
            padding: 12px 16px;
            background: transparent;
            color: var(--pkm-text-muted);
            font-weight: 600;
            cursor: pointer;
        }

        .pkm-modal-tab.is-active {
            background: #fff;
            color: var(--pkm-primary);
            box-shadow: 0 6px 18px rgba(42, 66, 95, 0.08);
        }

        .pkm-modal-panel[hidden] { display: none; }
        .pkm-modal-panel { max-height: min(55vh, 520px); overflow-y: auto; padding-right: 6px; }
        .pkm-modal__body { display: grid; gap: 14px; }

        @media (max-width: 1024px) {
            .pkm-monitoring-layout {
                grid-template-columns: 1fr;
            }

            .pkm-monitoring-calendar {
                grid-column: auto;
                order: 2;
            }

            .pkm-monitoring-list {
                order: 1;
            }
        }

        @media (max-width: 768px) {
            .pkm-dashboard-main--monitoring-jadwal {
                gap: 18px;
            }

            .pkm-topbar {
                gap: 12px;
                align-items: center;
            }

            .pkm-topbar__headline {
                flex: 1 1 auto;
                min-width: 0;
            }

            .pkm-topbar__actions {
                display: flex !important;
                align-items: center;
                justify-content: flex-end;
                gap: 8px;
                flex-wrap: nowrap;
                min-width: 0;
            }

            .pkm-topbar__login,
            .pkm-topbar__avatar {
                flex: 0 0 auto;
            }

            .pkm-section-head,
            .pkm-card__head {
                align-items: flex-start;
            }

            .pkm-monitoring-layout,
            .pkm-monitoring-list,
            .pkm-monitoring-calendar {
                min-width: 0;
            }

            .pkm-monitoring-list,
            .pkm-monitoring-calendar > .pkm-card,
            .pkm-calendar {
                width: 100%;
                max-width: 100%;
            }

            .pkm-calendar {
                overflow-x: auto;
                overflow-y: hidden;
                margin-inline: -2px;
                padding-bottom: 6px;
            }

            .pkm-calendar__weekdays,
            .pkm-calendar__grid {
                min-width: 0;
                width: 100%;
            }

            .pkm-calendar__grid {
                gap: 4px;
            }

            .pkm-calendar__day {
                min-height: 84px;
                padding: 8px;
                border-radius: 14px;
            }

            .pkm-calendar__weekdays span {
                font-size: 0.62rem;
            }
        }

        @media (max-width: 640px) {
            .pkm-dashboard-main--monitoring-jadwal {
                gap: 16px;
            }

            .pkm-calendar-head {
                grid-template-columns: 1fr;
                justify-items: stretch;
            }

            .pkm-calendar-head__main {
                width: 100%;
            }

            .pkm-calendar-head__actions {
                width: 100%;
                justify-content: stretch;
                flex-direction: column;
            }

            .pkm-calendar-head__actions > * {
                flex: 1 1 auto;
                min-width: 0;
                width: 100%;
            }

            .pkm-calendar__weekdays,
            .pkm-calendar__grid {
                min-width: 0;
            }
        }

        @media (max-width: 480px) {
            .pkm-topbar {
                gap: 8px;
            }

            .pkm-topbar__actions {
                gap: 6px;
            }

            .pkm-topbar__login {
                padding-inline: 12px;
            }

            .pkm-topbar__avatar {
                width: 40px;
                height: 40px;
            }

            .pkm-calendar__weekdays,
            .pkm-calendar__grid {
                gap: 4px;
            }

            .pkm-calendar__day {
                min-height: 72px;
                padding: 6px;
                border-radius: 12px;
            }
        }
    </style>
@endpush

@section('content')
    <section class="pkm-dashboard-main pkm-dashboard-main--monitoring-jadwal">
        <div class="pkm-section-head">
            <div>
                <h2 style="font-weight: bold">Monitoring Jadwal Kegiatan</h2>
            </div>
        </div>

        <div class="pkm-management-summary">
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-cyan-100 text-cyan-700"><i data-lucide="calendar-range" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['all'] }}</div>
                <div class="pkm-metric-card__label">Total Jadwal</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-emerald-100 text-emerald-700"><i data-lucide="play-circle" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['ongoing'] }}</div>
                <div class="pkm-metric-card__label">Sedang Berlangsung</div>
            </article>
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-amber-100 text-amber-700"><i data-lucide="clock-3" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['upcoming'] }}</div>
                <div class="pkm-metric-card__label">Belum Berlangsung</div>
            </article>
        </div>

        <section class="pkm-monitoring-layout">
            <div class="pkm-monitoring-list">
                <section class="pkm-card">
                    <div class="pkm-card__head">
                        <div>
                            <h3 style="font-weight: bold">Filter Jadwal</h3>
                            <br>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('admin.monitoring-jadwal') }}" class="pkm-monitoring-filter">
                        <div class="pkm-form-grid">
                            <div class="pkm-field">
                                <label for="date_from">Tanggal awal</label>
                                <input id="date_from" class="pkm-input" type="date" name="date_from" value="{{ $filters['date_from'] }}">
                            </div>
                            <div class="pkm-field">
                                <label for="date_to">Tanggal akhir</label>
                                <input id="date_to" class="pkm-input" type="date" name="date_to" value="{{ $filters['date_to'] }}">
                            </div>
                            <div class="pkm-field">
                                <label for="type">Jenis kegiatan</label>
                                <select id="type" class="pkm-input" name="type">
                                    <option value="all" @selected($filters['type'] === 'all')>Semua</option>
                                    <option value="layanan" @selected($filters['type'] === 'layanan')>Layanan</option>
                                    <option value="dinas_luar" @selected($filters['type'] === 'dinas_luar')>Dinas luar</option>
                                </select>
                            </div>
                            <div class="pkm-field">
                                <label for="phase">Status berlangsung</label>
                                <select id="phase" class="pkm-input" name="phase">
                                    <option value="all" @selected($filters['phase'] === 'all')>Semua</option>
                                    <option value="upcoming" @selected($filters['phase'] === 'upcoming')>Belum berlangsung</option>
                                    <option value="ongoing" @selected($filters['phase'] === 'ongoing')>Sedang berlangsung</option>
                                    <option value="completed" @selected($filters['phase'] === 'completed')>Sudah berlangsung</option>
                                </select>
                            </div>
                        </div>

                        <div class="pkm-form-actions">
                            <a href="{{ route('admin.monitoring-jadwal') }}" class="pkm-secondary-button"><span>Reset</span></a>
                            <button type="submit" class="pkm-primary-button"><span>Filter</span></button>
                        </div>
                    </form>
                </section>

                <section class="pkm-card">
                    <div class="pkm-card__head">
                        <div>
                            <h3 style="font-weight: bold">Daftar Jadwal</h3>
                            <br>
                        </div>
                    </div>

                    @if ($items->isEmpty())
                        <div class="pkm-empty-state">
                            <strong>Tidak ada agenda pada rentang ini.</strong>
                        </div>
                    @else
                        <div class="pkm-monitoring-items">
                            @foreach ($items as $item)
                                <article class="pkm-monitoring-item">
                                    <div class="pkm-monitoring-item__top">
                                        <div>
                                            <div class="pkm-monitoring-item__type">{{ $item['type_label'] }}</div>
                                            <strong>{{ $item['title'] }}</strong>
                                            <small>{{ $item['subtitle'] }}</small>
                                        </div>
                                        <div class="pkm-monitoring-item__badges">
                                            <span class="pkm-pill {{ $item['status_class'] }}">{{ $item['status_label'] }}</span>
                                        </div>
                                    </div>

                                    <div class="pkm-monitoring-item__grid">
                                        <div>
                                            <span>Tanggal</span>
                                            <strong>{{ $item['date_label'] }}</strong>
                                        </div>
                                        <div>
                                            <span>Waktu</span>
                                            <strong>{{ $item['time_label'] }}</strong>
                                        </div>
                                        <div>
                                            <span>Pegawai</span>
                                            <strong>{{ $item['people'] }}</strong>
                                        </div>
                                    </div>

                                    @if ($item['description'])
                                        <p>{{ $item['description'] }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <aside class="pkm-monitoring-calendar">
                <section class="pkm-card">
                    <div class="pkm-card__head pkm-calendar-head">
                        <div class="pkm-calendar-head__nav">
                            <a href="{{ route('admin.monitoring-jadwal', array_merge($calendarFilters['query'], ['calendar_month' => $calendarFilters['previous_month']])) }}" class="pkm-topbar__icon" aria-label="Bulan sebelumnya">
                                <i data-lucide="chevron-left" class="size-4"></i>
                            </a>
                        </div>
                        <div class="pkm-calendar-head__main">
                            <div class="pkm-calendar-head__title">
                                <h3 style="font-weight: bold">Kalender Kegiatan</h3>
                                <p>{{ $calendarMonthLabel }}</p>
                            </div>
                            <div class="pkm-calendar-head__actions">
                                <button type="button" class="pkm-secondary-button" id="btn-open-download-modal">
                                    <i data-lucide="eye" class="size-4"></i><span>Preview PDF</span>
                                </button>
                                <a href="{{ route('admin.monitoring-jadwal', array_merge($calendarFilters['query'], ['calendar_month' => $calendarFilters['current_month']])) }}" class="pkm-secondary-button"><i data-lucide="calendar-days" class="size-4"></i><span>Bulan Ini</span></a>
                            </div>
                        </div>
                        <div class="pkm-calendar-head__nav">
                            <a href="{{ route('admin.monitoring-jadwal', array_merge($calendarFilters['query'], ['calendar_month' => $calendarFilters['next_month']])) }}" class="pkm-topbar__icon" aria-label="Bulan berikutnya">
                                <i data-lucide="chevron-right" class="size-4"></i>
                            </a>
                        </div>
                    </div>

                    <div class="pkm-calendar">
                        <div class="pkm-calendar__weekdays">
                            @foreach ($weekdayLabels as $label)
                                <span>{{ $label }}</span>
                            @endforeach
                        </div>

                        <div class="pkm-calendar__grid">
                            @foreach ($calendarWeeks as $week)
                                @foreach ($week as $day)
                                    <button
                                        type="button"
                                        class="pkm-calendar__day {{ $day['in_month'] ? '' : 'is-outside' }} {{ $day['is_today'] ? 'is-today' : '' }} {{ $day['count'] > 0 ? 'is-clickable' : '' }}"
                                        data-calendar-day
                                        data-date-label="{{ $day['date']->translatedFormat('l, d F Y') }}"
                                        data-total="{{ $day['count'] }}"
                                        data-items='@json($day['items'])'
                                        data-assigned-pegawai-ids='@json($day['assigned_pegawai_ids'] ?? [])'
                                        aria-label="Lihat detail jadwal tanggal {{ $day['date']->translatedFormat('d F Y') }}"
                                    >
                                        <div class="pkm-calendar__date-row">
                                            <strong>{{ $day['date']->day }}</strong>
                                            @if ($day['count'] > 0)
                                                <span>{{ $day['count'] }}</span>
                                            @endif
                                        </div>

                                        @if ($day['count'] > 0)
                                            <div class="pkm-calendar__counts">
                                                @if ($day['layanan_count'] > 0)
                                                    <small>Layanan: {{ $day['layanan_count'] }}</small>
                                                @endif
                                                @if ($day['dinas_count'] > 0)
                                                    <small>Dinas: {{ $day['dinas_count'] }}</small>
                                                @endif
                                            </div>

                                            <div class="pkm-calendar__items">
                                                @foreach ($day['preview_items'] as $preview)
                                                    <div
                                                        class="pkm-calendar__item {{ $preview['type'] === 'layanan' ? 'is-layanan' : 'is-dinas' }}"
                                                        title="{{ $preview['title'] }} - {{ $preview['people'] }}"
                                                    >
                                                        {{ $preview['pj_initials'] }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </button>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </section>
            </aside>
        </section>

        <div class="pkm-modal" id="pkm-calendar-modal" hidden>
            <div class="pkm-modal__backdrop" data-modal-close></div>
            <div class="pkm-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="pkm-calendar-modal-title">
                <div class="pkm-modal__head">
                    <div>
                        <h3 id="pkm-calendar-modal-title">Detail Jadwal</h3>
                        <p id="pkm-calendar-modal-date">Tanggal dipilih</p>
                    </div>
                    <button type="button" class="pkm-modal__close" data-modal-close aria-label="Tutup detail jadwal">
                        <i data-lucide="x" class="size-4"></i>
                    </button>
                </div>

                <div class="pkm-modal__summary" id="pkm-calendar-modal-summary"></div>
                <div class="pkm-modal-tabs" role="tablist" aria-label="Filter pegawai jadwal">
                    <button type="button" class="pkm-modal-tab is-active" data-modal-tab="with-schedule">Pegawai Ada Jadwal</button>
                    <button type="button" class="pkm-modal-tab" data-modal-tab="without-schedule">Pegawai Tanpa Jadwal</button>
                </div>
                <div class="pkm-modal-panel" data-modal-panel="with-schedule">
                    <div class="pkm-modal__body" id="pkm-calendar-modal-body"></div>
                </div>
                <div class="pkm-modal-panel" data-modal-panel="without-schedule" hidden>
                    <div class="pkm-modal__body" id="pkm-calendar-modal-empty-body"></div>
                </div>
            </div>
        </div>

        <!-- Modal Unduh Jadwal -->
        <div class="pkm-modal" id="pkm-download-modal" hidden>
            <div class="pkm-modal__backdrop" id="btn-close-download-backdrop"></div>
            <div class="pkm-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="pkm-download-modal-title" style="padding: 30px; max-width: 550px;">
                <div class="pkm-modal__head" style="margin-bottom: 25px;">
                    <div>
                        <h3 id="pkm-download-modal-title" style="font-weight: bold; font-size: 1.25rem;">Preview Jadwal Kegiatan PDF</h3>
                        <p style="margin-top: 5px; color: var(--pkm-text-muted);">Silakan tentukan rentang tanggal jadwal kegiatan yang ingin ditampilkan</p>
                    </div>
                    <button type="button" class="pkm-modal__close" id="btn-close-download-x" aria-label="Tutup">
                        <i data-lucide="x" class="size-4"></i>
                    </button>
                </div>

                <form method="GET" action="{{ route('jadwal-kegiatan.export-global') }}" target="_blank" id="form-download-pdf">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                        <div class="pkm-field">
                            <label for="download_date_from" style="display: block; margin-bottom: 8px; font-weight: 500;">Tanggal Awal</label>
                            <input id="download_date_from" class="pkm-input" type="date" name="date_from" required style="width: 100%;">
                        </div>
                        <div class="pkm-field">
                            <label for="download_date_to" style="display: block; margin-bottom: 8px; font-weight: 500;">Tanggal Akhir</label>
                            <input id="download_date_to" class="pkm-input" type="date" name="date_to" required style="width: 100%;">
                        </div>
                    </div>

                    <div class="pkm-form-actions" style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #edf2f7; padding-top: 20px;">
                        <button type="button" class="pkm-secondary-button" id="btn-close-download-cancel" style="padding: 10px 20px;"><span>Batal</span></button>
                        <button type="submit" class="pkm-primary-button" id="btn-submit-download-pdf" style="padding: 10px 20px; display: inline-flex; align-items: center; gap: 8px;">
                            <i data-lucide="eye" class="size-4"></i><span>Tampilkan PDF</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('pkm-calendar-modal');
            const modalDate = document.getElementById('pkm-calendar-modal-date');
            const modalSummary = document.getElementById('pkm-calendar-modal-summary');
            const modalBody = document.getElementById('pkm-calendar-modal-body');
            const modalEmptyBody = document.getElementById('pkm-calendar-modal-empty-body');
            const dayButtons = document.querySelectorAll('[data-calendar-day]');
            const closeButtons = document.querySelectorAll('[data-modal-close]');
            const tabButtons = document.querySelectorAll('[data-modal-tab]');
            const panels = document.querySelectorAll('[data-modal-panel]');
            const activePegawai = @json($activePegawaiForModal ?? []);

            if (!modal || !modalDate || !modalSummary || !modalBody || !modalEmptyBody || dayButtons.length === 0) {
                return;
            }

            const typeClassMap = {
                layanan: 'is-layanan',
                dinas_luar: 'is-dinas',
            };

            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const closeModal = () => {
                modal.hidden = true;
                document.body.classList.remove('pkm-modal-open');
            };

            const openModal = () => {
                modal.hidden = false;
                document.body.classList.add('pkm-modal-open');

                if (window.lucide) {
                    window.lucide.createIcons();
                }
            };

            const setActiveTab = (tabName) => {
                tabButtons.forEach((button) => {
                    button.classList.toggle('is-active', button.dataset.modalTab === tabName);
                });

                panels.forEach((panel) => {
                    panel.hidden = panel.dataset.modalPanel !== tabName;
                });
            };

            dayButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    const items = JSON.parse(this.dataset.items || '[]');
                    const dateLabel = this.dataset.dateLabel || 'Tanggal dipilih';
                    const total = Number(this.dataset.total || 0);
                    const assignedIds = new Set(
                        JSON.parse(this.dataset.assignedPegawaiIds || '[]').map(String)
                    );
                    const availablePegawai = activePegawai.filter((pegawai) => !assignedIds.has(String(pegawai.id)));

                    modalDate.textContent = dateLabel;
                    modalSummary.innerHTML = total > 0
                        ? '<span class="pkm-pill is-blue">' + total + ' agenda</span>'
                        : '<span class="pkm-pill is-amber">Tidak ada jadwal</span><small>Belum ada kegiatan yang tercatat pada tanggal ini.</small>';

                    modalBody.innerHTML = items.length > 0
                        ? items.map((item) => `
                            <article class="pkm-modal-schedule">
                                <div class="pkm-modal-schedule__top">
                                    <div>
                                        <span class="pkm-calendar__item ${typeClassMap[item.type] ?? 'is-layanan'}">${escapeHtml(item.type_label)}</span>
                                        <strong>${escapeHtml(item.title)}</strong>
                                        <small>${escapeHtml(item.subtitle ?? '-')}</small>
                                    </div>
                                    <span class="pkm-pill ${escapeHtml(item.status_class ?? 'is-blue')}">${escapeHtml(item.status_label ?? item.meta_status ?? item.phase_label)}</span>
                                </div>
                                <div class="pkm-modal-schedule__grid">
                                    <div>
                                        <span>Waktu</span>
                                        <strong>${escapeHtml(item.time_label)}</strong>
                                    </div>
                                    <div>
                                        <span>Pegawai</span>
                                        <strong>${escapeHtml(item.people)}</strong>
                                    </div>
                                    <div>
                                        <span>Tanggal</span>
                                        <strong>${escapeHtml(item.date_label)}</strong>
                                    </div>
                                </div>
                                ${item.description ? `<p>${escapeHtml(item.description)}</p>` : ''}
                            </article>
                        `).join('')
                        : '<div class="pkm-empty-state"><strong>Tidak ada jadwal pada tanggal ini.</strong><p>Coba pilih tanggal lain pada kalender.</p></div>';

                    modalEmptyBody.innerHTML = availablePegawai.length > 0
                        ? availablePegawai.map((pegawai) => `
                            <article class="pkm-modal-schedule">
                                <div class="pkm-modal-schedule__top">
                                    <div>
                                        <span class="pkm-calendar__item is-amber">Pegawai Tanpa Jadwal</span>
                                        <strong>${escapeHtml(pegawai.nama)}</strong>
                                        <small>${escapeHtml(pegawai.jabatan ?? '-')}</small>
                                    </div>
                                    <span class="pkm-pill is-amber">Kosong</span>
                                </div>
                            </article>
                        `).join('')
                        : '<div class="pkm-empty-state"><strong>Semua pegawai sudah memiliki jadwal.</strong><p>Tidak ada pegawai kosong pada tanggal ini.</p></div>';

                    setActiveTab('with-schedule');
                    openModal();
                });
            });

            tabButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    setActiveTab(this.dataset.modalTab || 'with-schedule');
                });
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !modal.hidden) {
                    closeModal();
                }
            });

            // Logika Modal Unduh PDF
            const downloadModal = document.getElementById('pkm-download-modal');
            const btnOpenDownload = document.getElementById('btn-open-download-modal');
            const btnCloseDownloadBackdrop = document.getElementById('btn-close-download-backdrop');
            const btnCloseDownloadX = document.getElementById('btn-close-download-x');
            const btnCloseDownloadCancel = document.getElementById('btn-close-download-cancel');
            const inputDownloadDateFrom = document.getElementById('download_date_from');
            const inputDownloadDateTo = document.getElementById('download_date_to');

            const openDownloadModal = () => {
                // Set default tanggal awal: Hari ini
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                const formattedToday = `${year}-${month}-${day}`;

                // Set default tanggal akhir: 1 bulan kedepan
                const nextMonthDate = new Date();
                nextMonthDate.setMonth(nextMonthDate.getMonth() + 1);
                const nextYear = nextMonthDate.getFullYear();
                const nextMonth = String(nextMonthDate.getMonth() + 1).padStart(2, '0');
                const nextDay = String(nextMonthDate.getDate()).padStart(2, '0');
                const formattedNextMonth = `${nextYear}-${nextMonth}-${nextDay}`;

                inputDownloadDateFrom.value = formattedToday;
                inputDownloadDateTo.value = formattedNextMonth;

                downloadModal.hidden = false;
                document.body.classList.add('pkm-modal-open');
                
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            };

            const closeDownloadModal = () => {
                downloadModal.hidden = true;
                document.body.classList.remove('pkm-modal-open');
            };

            if (btnOpenDownload) {
                btnOpenDownload.addEventListener('click', openDownloadModal);
            }

            [btnCloseDownloadBackdrop, btnCloseDownloadX, btnCloseDownloadCancel].forEach(btn => {
                if (btn) {
                    btn.addEventListener('click', closeDownloadModal);
                }
            });

            // Tutup modal unduh jika Escape ditekan
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !downloadModal.hidden) {
                    closeDownloadModal();
                }
            });
        });
    </script>
@endpush
