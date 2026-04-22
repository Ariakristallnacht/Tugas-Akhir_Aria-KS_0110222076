@extends('layouts.dashboard')

@php
    $title = 'Pencarian | Puskesmas Bunar';
    $heading = 'Hasil Pencarian';
    $totalResults = collect($results)->sum(fn ($items) => $items->count());
@endphp

@section('content')
    <section class="pkm-dashboard-main">
        <div class="pkm-card">
            <div class="pkm-card__head">
                <div>
                    <h2 style="font-weight: bold">Pencarian Global</h2>
                    <p>
                        @if ($query !== '')
                            Menampilkan hasil untuk <strong>"{{ $query }}"</strong>.
                        @else
                            Masukkan kata kunci dari navbar untuk mencari data.
                        @endif
                    </p>
                </div>
            </div>

            @if ($query === '')
                <div class="pkm-empty-state">
                    <strong>Belum ada kata kunci pencarian.</strong>
                    <p>Coba cari nama pegawai, kegiatan, lokasi, laporan, atau pengajuan dinas.</p>
                </div>
            @elseif ($totalResults === 0)
                <div class="pkm-empty-state">
                    <strong>Data tidak ditemukan.</strong>
                    <p>Coba gunakan kata kunci lain yang lebih singkat atau lebih spesifik.</p>
                </div>
            @else
                <div class="pkm-management-summary">
                    <article class="pkm-metric-card">
                        <div class="pkm-metric-card__icon bg-cyan-100 text-cyan-700"><i data-lucide="search" class="size-5"></i></div>
                        <div class="pkm-metric-card__value">{{ $totalResults }}</div>
                        <div class="pkm-metric-card__label">Total Hasil</div>
                    </article>
                    @if ($results['pegawai']->isNotEmpty())
                        <article class="pkm-metric-card">
                            <div class="pkm-metric-card__icon bg-emerald-100 text-emerald-700"><i data-lucide="users" class="size-5"></i></div>
                            <div class="pkm-metric-card__value">{{ $results['pegawai']->count() }}</div>
                            <div class="pkm-metric-card__label">Pegawai</div>
                        </article>
                    @endif
                    @if ($results['jadwal']->isNotEmpty())
                        <article class="pkm-metric-card">
                            <div class="pkm-metric-card__icon bg-amber-100 text-amber-700"><i data-lucide="calendar-range" class="size-5"></i></div>
                            <div class="pkm-metric-card__value">{{ $results['jadwal']->count() }}</div>
                            <div class="pkm-metric-card__label">Jadwal</div>
                        </article>
                    @endif
                    @if ($results['laporan']->isNotEmpty())
                        <article class="pkm-metric-card">
                            <div class="pkm-metric-card__icon bg-sky-100 text-sky-700"><i data-lucide="file-text" class="size-5"></i></div>
                            <div class="pkm-metric-card__value">{{ $results['laporan']->count() }}</div>
                            <div class="pkm-metric-card__label">Laporan</div>
                        </article>
                    @endif
                    @if ($results['pengajuan']->isNotEmpty())
                        <article class="pkm-metric-card">
                            <div class="pkm-metric-card__icon bg-rose-100 text-rose-700"><i data-lucide="briefcase-business" class="size-5"></i></div>
                            <div class="pkm-metric-card__value">{{ $results['pengajuan']->count() }}</div>
                            <div class="pkm-metric-card__label">Pengajuan</div>
                        </article>
                    @endif
                </div>

                @if ($results['pegawai']->isNotEmpty())
                    <section class="pkm-card pkm-table-card" style="margin-top: 24px;">
                        <div class="pkm-card__head">
                            <div>
                                <h3 style="font-weight: bold">Pegawai</h3>
                            </div>
                        </div>

                        <div class="pkm-table pkm-table--pegawai">
                            <div class="pkm-table__head">
                                <span>Pegawai</span>
                                <span>Jabatan</span>
                                <span>Akun</span>
                                <span>Status</span>
                                <span>Aksi</span>
                            </div>

                            @foreach ($results['pegawai'] as $pegawai)
                                <div class="pkm-table__row">
                                    <div data-label="Pegawai">
                                        <strong>{{ $pegawai->nama }}</strong>
                                        <small>{{ $pegawai->nip }}</small>
                                    </div>
                                    <div data-label="Jabatan">
                                        <strong>{{ $pegawai->jabatan }}</strong>
                                        <small>{{ $pegawai->unit_kerja }}</small>
                                    </div>
                                    <div data-label="Akun">
                                        <strong>{{ $pegawai->user?->email ?? 'Belum punya akun' }}</strong>
                                        <small>{{ $pegawai->user?->role?->nama ?? 'Role belum diatur' }}</small>
                                    </div>
                                    <div data-label="Status">
                                        <span class="pkm-pill {{ $pegawai->is_aktif ? 'is-green' : 'is-amber' }}">{{ $pegawai->is_aktif ? 'Aktif' : 'Nonaktif' }}</span>
                                    </div>
                                    <div data-label="Aksi">
                                        <a href="{{ route('admin.pegawai.edit', $pegawai) }}" class="pkm-text-link">
                                            <i data-lucide="arrow-right" class="size-4"></i>
                                            <span>Buka</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($results['jadwal']->isNotEmpty())
                    <section class="pkm-card pkm-table-card" style="margin-top: 24px;">
                        <div class="pkm-card__head">
                            <div>
                                <h3 style="font-weight: bold">Jadwal Kegiatan</h3>
                            </div>
                        </div>

                        <div class="pkm-table pkm-table--laporan">
                            <div class="pkm-table__head">
                                <span>Jadwal</span>
                                <span>Petugas</span>
                                <span>Lokasi</span>
                                <span>Aksi</span>
                            </div>

                            @foreach ($results['jadwal'] as $jadwal)
                                <div class="pkm-table__row">
                                    <div data-label="Jadwal">
                                        <strong>{{ $jadwal->kegiatan?->nama_kegiatan ?? 'Kegiatan' }}</strong>
                                        <small>{{ $jadwal->tanggal?->translatedFormat('d M Y') ?? '-' }} • {{ $jadwal->waktu_mulai?->format('H:i') ?? '-' }} - {{ $jadwal->waktu_selesai?->format('H:i') ?? '-' }}</small>
                                    </div>
                                    <div data-label="Petugas">
                                        <strong>{{ $jadwal->pegawai->pluck('nama')->take(2)->implode(', ') ?: 'Belum ada petugas' }}</strong>
                                        <small>{{ $jadwal->pegawai->count() }} pegawai</small>
                                    </div>
                                    <div data-label="Lokasi">
                                        <strong>{{ $jadwal->lokasi }}</strong>
                                        <small>{{ \Illuminate\Support\Str::limit($jadwal->keterangan ?: 'Tanpa keterangan', 70) }}</small>
                                    </div>
                                    <div data-label="Aksi">
                                        @if ($roleCode === 'pj_penjadwalan')
                                            <a href="{{ route('pj.jadwal-kegiatan.edit', $jadwal) }}" class="pkm-text-link">
                                                <i data-lucide="arrow-right" class="size-4"></i>
                                                <span>Buka</span>
                                            </a>
                                        @elseif ($roleCode === 'admin')
                                            <a href="{{ route('admin.monitoring-jadwal', ['date_from' => $jadwal->tanggal?->format('Y-m-d'), 'date_to' => $jadwal->tanggal?->format('Y-m-d')]) }}" class="pkm-text-link">
                                                <i data-lucide="arrow-right" class="size-4"></i>
                                                <span>Lihat</span>
                                            </a>
                                        @else
                                            <a href="{{ route('pegawai.jadwal-kegiatan', ['date_from' => $jadwal->tanggal?->format('Y-m-d'), 'date_to' => $jadwal->tanggal?->format('Y-m-d'), 'scope' => 'mine']) }}" class="pkm-text-link">
                                                <i data-lucide="arrow-right" class="size-4"></i>
                                                <span>Lihat</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($results['laporan']->isNotEmpty())
                    <section class="pkm-card pkm-table-card" style="margin-top: 24px;">
                        <div class="pkm-card__head">
                            <div>
                                <h3 style="font-weight: bold">Laporan Kegiatan</h3>
                            </div>
                        </div>

                        <div class="pkm-table pkm-table--laporan">
                            <div class="pkm-table__head">
                                <span>Laporan</span>
                                <span>Pelaksana</span>
                                <span>Waktu</span>
                                <span>Aksi</span>
                            </div>

                            @foreach ($results['laporan'] as $laporan)
                                <div class="pkm-table__row">
                                    <div data-label="Laporan">
                                        <strong>{{ $laporan->jadwal?->kegiatan?->nama_kegiatan ?? 'Kegiatan tidak ditemukan' }}</strong>
                                        <small>{{ \Illuminate\Support\Str::limit($laporan->laporan, 110) }}</small>
                                    </div>
                                    <div data-label="Pelaksana">
                                        <strong>{{ $laporan->pegawai?->nama ?? '-' }}</strong>
                                        <small>{{ $laporan->jadwal?->lokasi ?? 'Lokasi belum diisi' }}</small>
                                    </div>
                                    <div data-label="Waktu">
                                        <strong>{{ $laporan->tanggal?->translatedFormat('d M Y') ?? '-' }}</strong>
                                        <small>Dibuat {{ $laporan->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</small>
                                    </div>
                                    <div data-label="Aksi">
                                        @if ($roleCode === 'pj_penjadwalan')
                                            <a href="{{ route('pj.laporan-kegiatan.edit', $laporan) }}" class="pkm-text-link">
                                                <i data-lucide="arrow-right" class="size-4"></i>
                                                <span>Buka</span>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.monitoring-laporan', ['search' => $query]) }}" class="pkm-text-link">
                                                <i data-lucide="arrow-right" class="size-4"></i>
                                                <span>Lihat</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($results['pengajuan']->isNotEmpty())
                    <section class="pkm-card pkm-table-card" style="margin-top: 24px;">
                        <div class="pkm-card__head">
                            <div>
                                <h3 style="font-weight: bold">Pengajuan Dinas</h3>
                            </div>
                        </div>

                        <div class="pkm-table pkm-table--pegawai">
                            <div class="pkm-table__head">
                                <span>Pengajuan</span>
                                <span>Tujuan</span>
                                <span>Status</span>
                                <span>Aksi</span>
                            </div>

                            @foreach ($results['pengajuan'] as $pengajuan)
                                <div class="pkm-table__row">
                                    <div data-label="Pengajuan">
                                        <strong>{{ \Illuminate\Support\Str::limit($pengajuan->kegiatan, 70) }}</strong>
                                        <small>
                                            @if ($roleCode === 'pj_penjadwalan')
                                                {{ $pengajuan->pegawai?->nama ?? '-' }}
                                            @else
                                                Diajukan {{ $pengajuan->tanggal_pengajuan?->translatedFormat('d M Y') ?? '-' }}
                                            @endif
                                        </small>
                                    </div>
                                    <div data-label="Tujuan">
                                        <strong>{{ $pengajuan->tujuan }}</strong>
                                        <small>{{ $pengajuan->tanggal_mulai?->translatedFormat('d M Y') ?? '-' }} s.d. {{ $pengajuan->tanggal_selesai?->translatedFormat('d M Y') ?? '-' }}</small>
                                    </div>
                                    <div data-label="Status">
                                        <span class="pkm-pill {{ $pengajuan->status === 'disetujui' ? 'is-green' : ($pengajuan->status === 'ditolak' ? 'is-amber' : 'is-blue') }}">{{ ucfirst($pengajuan->status) }}</span>
                                    </div>
                                    <div data-label="Aksi">
                                        @if ($roleCode === 'pegawai' && in_array($pengajuan->status, ['diajukan', 'dibatalkan'], true))
                                            <a href="{{ route('pegawai.pengajuan-dinas.edit', $pengajuan) }}" class="pkm-text-link">
                                                <i data-lucide="arrow-right" class="size-4"></i>
                                                <span>Buka</span>
                                            </a>
                                        @else
                                            <a href="{{ $roleCode === 'pj_penjadwalan' ? route('pj.verifikasi-pengajuan-dinas.index') : route('pegawai.pengajuan-dinas.index') }}" class="pkm-text-link">
                                                <i data-lucide="arrow-right" class="size-4"></i>
                                                <span>Lihat</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endif
        </div>
    </section>
@endsection
