@extends('layouts.dashboard')

@php
    $title = 'Jadwal Kegiatan | Puskesmas Bunar';
    $heading = 'Jadwal Kegiatan';
    $weekdayLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
@endphp

@push('styles')
    <style>
        @media (min-width: 1280px) {
            .pkm-management-summary--single-row {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .pkm-monitoring-item__actions {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 16px;
        }

        .pkm-monitoring-item__delete {
            border: 0;
            background: transparent;
            box-shadow: none;
            padding: 0;
            color: #c25a53;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
        }
    </style>
@endpush

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-section-head">
            <div>
                <h2 style="font-weight: bold">Jadwal Kegiatan</h2>
            </div>
            <a href="{{ route('pj.jadwal-kegiatan.create') }}" class="pkm-primary-button">Tambah Jadwal</a>
        </div>

        @include('admin.partials.flash')

        <div class="pkm-management-summary pkm-management-summary--single-row">
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
            <article class="pkm-metric-card">
                <div class="pkm-metric-card__icon bg-slate-100 text-slate-700"><i data-lucide="briefcase-business" class="size-5"></i></div>
                <div class="pkm-metric-card__value">{{ $summary['approved_dinas'] }}</div>
                <div class="pkm-metric-card__label">Dinas Disetujui</div>
            </article>
        </div>

        <section class="pkm-monitoring-layout">
            <div class="pkm-monitoring-list">
                <section class="pkm-card">
                    <div class="pkm-card__head">
                        <div>
                            <h3 style="font-weight: bold">Filter Penjadwalan</h3>
                            <br>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('pj.jadwal-kegiatan.index') }}" class="pkm-monitoring-filter">
                        <div class="pkm-form-grid">
                            <div class="pkm-field">
                                <label for="month">Bulan kalender</label>
                                <input id="month" class="pkm-input" type="month" name="month" value="{{ $filters['month'] }}">
                            </div>
                            <div class="pkm-field">
                                <label for="reference_date">Tanggal acuan status</label>
                                <input id="reference_date" class="pkm-input" type="date" name="reference_date" value="{{ $filters['reference_date'] }}">
                            </div>
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
                                    <option value="layanan" @selected($filters['type'] === 'layanan')>Jadwal layanan</option>
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
                            <a href="{{ route('pj.jadwal-kegiatan.index') }}" class="pkm-secondary-button">Reset</a>
                            <button type="submit" class="pkm-primary-button">Terapkan Filter</button>
                        </div>
                    </form>
                </section>

                <section class="pkm-card">
                    <div class="pkm-card__head">
                        <div>
                            <h3 style="font-weight: bold">Daftar Jadwal</h3>
                        </div>
                    </div>

                    @if ($items->isEmpty())
                        <div class="pkm-empty-state">
                            <strong>Tidak ada jadwal pada rentang ini.</strong>
                            <p>Coba ubah bulan, rentang tanggal, atau status penjadwalan yang dipilih.</p>
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
                                            <span class="pkm-pill {{ $item['phase'] === 'ongoing' ? 'is-green' : ($item['phase'] === 'upcoming' ? 'is-blue' : 'is-amber') }}">
                                                {{ $item['phase_label'] }}
                                            </span>
                                            <span class="pkm-monitoring-item__meta">{{ $item['meta_status'] }}</span>
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
                                            <span>Petugas</span>
                                            <strong>{{ $item['people'] }}</strong>
                                        </div>
                                    </div>

                                    @if ($item['description'])
                                        <p>{{ $item['description'] }}</p>
                                    @endif

                                    @if ($item['reference_note'])
                                        <p style="color: var(--pkm-text-muted); margin-top: 12px;">{{ $item['reference_note'] }}</p>
                                    @endif

                                    @if ($item['edit_url'] || $item['delete_url'])
                                        <div class="pkm-monitoring-item__actions">
                                            @if ($item['edit_url'])
                                                <a href="{{ $item['edit_url'] }}" class="pkm-text-link inline-flex items-center gap-2">
                                                    <i data-lucide="pencil" class="size-4"></i>
                                                    <span>Edit</span>
                                                </a>
                                            @endif
                                            @if ($item['delete_url'])
                                                <form method="POST" action="{{ $item['delete_url'] }}" onsubmit="return confirm('Hapus jadwal kegiatan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="pkm-monitoring-item__delete">
                                                        <i data-lucide="trash-2" class="size-4"></i>
                                                        <span>Hapus</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <aside class="pkm-monitoring-calendar">
                <section class="pkm-card">
                    <div class="pkm-card__head">
                        <div>
                            <h3 style="font-weight:bold">Kalender Jadwal</h3>
                            <p>{{ $calendarMonthLabel }}</p>
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
                <div class="pkm-modal__body" id="pkm-calendar-modal-body"></div>
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
            const dayButtons = document.querySelectorAll('[data-calendar-day]');
            const closeButtons = document.querySelectorAll('[data-modal-close]');

            if (!modal || !modalDate || !modalSummary || !modalBody || dayButtons.length === 0) {
                return;
            }

            const phaseClassMap = {
                ongoing: 'is-green',
                upcoming: 'is-blue',
                completed: 'is-amber',
            };

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

            dayButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    const items = JSON.parse(this.dataset.items || '[]');
                    const dateLabel = this.dataset.dateLabel || 'Tanggal dipilih';
                    const total = Number(this.dataset.total || 0);

                    modalDate.textContent = dateLabel;
                    modalSummary.innerHTML = total > 0
                        ? '<span class="pkm-pill is-blue">' + total + ' agenda</span><small>Tanggal ini memadukan layanan poli dan dinas luar yang perlu diperhitungkan PJ.</small>'
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
                                    <span class="pkm-pill ${phaseClassMap[item.phase] ?? 'is-blue'}">${escapeHtml(item.phase_label)}</span>
                                </div>
                                <div class="pkm-modal-schedule__grid">
                                    <div>
                                        <span>Waktu</span>
                                        <strong>${escapeHtml(item.time_label)}</strong>
                                    </div>
                                    <div>
                                        <span>Petugas</span>
                                        <strong>${escapeHtml(item.people)}</strong>
                                    </div>
                                    <div>
                                        <span>Status</span>
                                        <strong>${escapeHtml(item.meta_status)}</strong>
                                    </div>
                                    <div>
                                        <span>Tanggal</span>
                                        <strong>${escapeHtml(item.date_label)}</strong>
                                    </div>
                                </div>
                                ${item.description ? `<p>${escapeHtml(item.description)}</p>` : ''}
                                ${item.reference_note ? `<p>${escapeHtml(item.reference_note)}</p>` : ''}
                                ${item.edit_url ? `
                                    <div class="pkm-monitoring-item__actions">
                                        <a href="${escapeHtml(item.edit_url)}" class="pkm-text-link inline-flex items-center gap-2">
                                            <i data-lucide="pencil" class="size-4"></i>
                                            <span>Edit</span>
                                        </a>
                                    </div>
                                ` : ''}
                            </article>
                        `).join('')
                        : '<div class="pkm-empty-state"><strong>Tidak ada jadwal pada tanggal ini.</strong><p>Coba pilih tanggal lain pada kalender.</p></div>';

                    openModal();
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
        });
    </script>
@endpush
