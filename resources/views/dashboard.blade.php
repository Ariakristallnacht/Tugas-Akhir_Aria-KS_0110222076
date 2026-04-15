@extends('layouts.dashboard')

@php
    $title = 'Dashboard Puskesmas Bunar';
    $heading = 'Dashboard Penjadwalan';
@endphp

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.4fr_0.9fr]">
        <div class="flex flex-col gap-6">
            <div class="pkm-hero relative overflow-hidden rounded-[34px] px-6 py-7 text-white xl:px-8 xl:py-8">
                <div class="pkm-hero__pattern"></div>
                <div class="relative z-10 flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
                    <div class="max-w-2xl">
                        <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/85">
                            <i data-lucide="shield-plus" class="size-3.5"></i>
                            Layanan Kesehatan Terjadwal
                        </div>
                        <h2 class="text-3xl font-semibold leading-tight xl:text-4xl">Koordinasi jadwal layanan dan dinas luar dalam satu dashboard yang tenang, rapi, dan mudah dipantau.</h2>
                        <p class="mt-4 max-w-xl text-sm leading-7 text-white/78 xl:text-base">
                            Template ini sudah disesuaikan dari aset Midone di folder `public/template` dan diarahkan ke nuansa hijau soft agar lebih sesuai untuk sistem informasi Puskesmas Bunar.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 xl:min-w-[320px]">
                        <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                            <div class="text-xs uppercase tracking-[0.18em] text-white/70">Layanan Hari Ini</div>
                            <div class="mt-3 text-3xl font-semibold">12</div>
                            <div class="mt-1 text-sm text-white/70">5 poli, 7 layanan umum</div>
                        </div>
                        <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                            <div class="text-xs uppercase tracking-[0.18em] text-white/70">Dinas Luar</div>
                            <div class="mt-3 text-3xl font-semibold">4</div>
                            <div class="mt-1 text-sm text-white/70">Menunggu verifikasi 2</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="pkm-card rounded-[28px] p-5">
                    <div class="pkm-icon bg-emerald-100 text-emerald-700"><i data-lucide="users-round" class="size-5"></i></div>
                    <div class="mt-5 text-sm text-slate-500">Total Pegawai Aktif</div>
                    <div class="mt-2 text-3xl font-semibold text-slate-800">48</div>
                    <div class="mt-2 text-xs text-emerald-700">Terdiri dari medis, administrasi, dan penanggung jawab.</div>
                </div>
                <div class="pkm-card rounded-[28px] p-5">
                    <div class="pkm-icon bg-lime-100 text-lime-700"><i data-lucide="calendar-check-2" class="size-5"></i></div>
                    <div class="mt-5 text-sm text-slate-500">Jadwal Tersusun</div>
                    <div class="mt-2 text-3xl font-semibold text-slate-800">31</div>
                    <div class="mt-2 text-xs text-lime-700">Periode minggu berjalan sudah hampir penuh.</div>
                </div>
                <div class="pkm-card rounded-[28px] p-5">
                    <div class="pkm-icon bg-teal-100 text-teal-700"><i data-lucide="clipboard-list" class="size-5"></i></div>
                    <div class="mt-5 text-sm text-slate-500">Laporan Masuk</div>
                    <div class="mt-2 text-3xl font-semibold text-slate-800">18</div>
                    <div class="mt-2 text-xs text-teal-700">6 laporan menunggu tindak lanjut PJ penjadwalan.</div>
                </div>
                <div class="pkm-card rounded-[28px] p-5">
                    <div class="pkm-icon bg-amber-100 text-amber-700"><i data-lucide="siren" class="size-5"></i></div>
                    <div class="mt-5 text-sm text-slate-500">Butuh Atensi</div>
                    <div class="mt-2 text-3xl font-semibold text-slate-800">3</div>
                    <div class="mt-2 text-xs text-amber-700">Ada benturan jadwal dan dinas luar yang perlu dicek.</div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                <div class="pkm-card rounded-[30px] p-6">
                    <div class="flex flex-col gap-3 border-b border-slate-100 pb-5 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">Jadwal Layanan Terdekat</h3>
                            <p class="mt-1 text-sm text-slate-500">Ringkasan penugasan pelayanan dan dinas luar yang akan berjalan.</p>
                        </div>
                        <button class="inline-flex items-center gap-2 rounded-2xl bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700">
                            <i data-lucide="plus" class="size-4"></i>
                            Tambah Jadwal
                        </button>
                    </div>

                    <div class="mt-5 overflow-hidden rounded-[24px] border border-emerald-100/80">
                        <div class="grid grid-cols-[1.1fr_1fr_0.9fr_0.9fr] bg-emerald-50/80 px-5 py-4 text-xs font-semibold uppercase tracking-[0.15em] text-emerald-800">
                            <div>Kegiatan</div>
                            <div>Penugasan</div>
                            <div>Waktu</div>
                            <div>Status</div>
                        </div>
                        <div class="divide-y divide-slate-100 bg-white">
                            <div class="grid grid-cols-[1.1fr_1fr_0.9fr_0.9fr] items-center px-5 py-4 text-sm">
                                <div><div class="font-semibold text-slate-800">Pelayanan Poli Umum</div><div class="mt-1 text-slate-500">Gedung utama lantai 1</div></div>
                                <div class="text-slate-600">dr. Rina, 2 perawat</div>
                                <div class="text-slate-600">08.00 - 12.00</div>
                                <div><span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Terjadwal</span></div>
                            </div>
                            <div class="grid grid-cols-[1.1fr_1fr_0.9fr_0.9fr] items-center px-5 py-4 text-sm">
                                <div><div class="font-semibold text-slate-800">Imunisasi Keliling</div><div class="mt-1 text-slate-500">Posyandu Melati</div></div>
                                <div class="text-slate-600">Bidan Siska, 1 admin</div>
                                <div class="text-slate-600">09.00 - 11.30</div>
                                <div><span class="rounded-full bg-lime-100 px-3 py-1 text-xs font-semibold text-lime-700">Berjalan</span></div>
                            </div>
                            <div class="grid grid-cols-[1.1fr_1fr_0.9fr_0.9fr] items-center px-5 py-4 text-sm">
                                <div><div class="font-semibold text-slate-800">Penyuluhan Gizi</div><div class="mt-1 text-slate-500">Balai warga RW 03</div></div>
                                <div class="text-slate-600">Ahli Gizi, 1 promkes</div>
                                <div class="text-slate-600">13.00 - 15.00</div>
                                <div><span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Perlu Verifikasi</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pkm-card rounded-[30px] p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">Agenda Cepat</h3>
                            <p class="mt-1 text-sm text-slate-500">Aksi yang sering dipakai operator.</p>
                        </div>
                        <div class="rounded-2xl bg-emerald-50 p-3 text-emerald-700"><i data-lucide="sparkles" class="size-5"></i></div>
                    </div>

                    <div class="mt-5 grid gap-3">
                        <a href="#" class="pkm-action">
                            <span class="pkm-action__icon"><i data-lucide="calendar-plus" class="size-4"></i></span>
                            <span><span class="block font-semibold text-slate-800">Susun Jadwal Layanan</span><span class="block text-sm text-slate-500">Atur pelayanan poli, imunisasi, dan layanan rutin.</span></span>
                        </a>
                        <a href="#" class="pkm-action">
                            <span class="pkm-action__icon"><i data-lucide="briefcase-business" class="size-4"></i></span>
                            <span><span class="block font-semibold text-slate-800">Verifikasi Dinas Luar</span><span class="block text-sm text-slate-500">Periksa pengajuan kegiatan lapangan pegawai.</span></span>
                        </a>
                        <a href="#" class="pkm-action">
                            <span class="pkm-action__icon"><i data-lucide="file-check-2" class="size-4"></i></span>
                            <span><span class="block font-semibold text-slate-800">Tinjau Laporan</span><span class="block text-sm text-slate-500">Pantau laporan kegiatan yang sudah masuk.</span></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <aside class="flex flex-col gap-6">
            <div class="pkm-card rounded-[30px] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">Status Operasional</h3>
                        <p class="mt-1 text-sm text-slate-500">Gambaran cepat kondisi hari ini.</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 p-3 text-emerald-700"><i data-lucide="heart-handshake" class="size-5"></i></div>
                </div>
                <div class="mt-6 flex flex-col gap-4">
                    <div class="pkm-status-row"><span>Pelayanan aktif</span><strong>5 unit</strong></div>
                    <div class="pkm-status-row"><span>Pegawai dinas luar</span><strong>8 orang</strong></div>
                    <div class="pkm-status-row"><span>Laporan selesai</span><strong>12 berkas</strong></div>
                    <div class="pkm-status-row"><span>Verifikasi tertunda</span><strong>2 pengajuan</strong></div>
                </div>
            </div>

            <div class="pkm-card rounded-[30px] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">Pegawai Siaga</h3>
                        <p class="mt-1 text-sm text-slate-500">Siap ditugaskan untuk layanan tambahan.</p>
                    </div>
                    <a href="#" class="text-sm font-medium text-emerald-700">Lihat semua</a>
                </div>

                <div class="mt-5 flex flex-col gap-4">
                    @foreach ([['nama' => 'dr. Rina Permata', 'jabatan' => 'Dokter Umum'], ['nama' => 'Siska Anggraini', 'jabatan' => 'Bidan Koordinator'], ['nama' => 'Fauzan Pratama', 'jabatan' => 'Promosi Kesehatan']] as $pegawai)
                        <div class="flex items-center gap-4 rounded-[24px] border border-slate-100 bg-slate-50/80 p-4">
                            <div class="flex size-12 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-semibold text-emerald-700">{{ strtoupper(substr($pegawai['nama'], 0, 1)) }}</div>
                            <div class="min-w-0 flex-1">
                                <div class="truncate font-semibold text-slate-800">{{ $pegawai['nama'] }}</div>
                                <div class="truncate text-sm text-slate-500">{{ $pegawai['jabatan'] }}</div>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">Siaga</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pkm-card rounded-[30px] p-6">
                <div class="rounded-[28px] bg-emerald-50 p-5">
                    <div class="flex items-center gap-3 text-emerald-700">
                        <i data-lucide="shield-plus" class="size-5"></i>
                        <h3 class="text-base font-semibold">Tema Sudah Disesuaikan</h3>
                    </div>
                    <p class="mt-3 text-sm leading-7 text-emerald-800/80">
                        Warna default template sudah diarahkan ke spektrum hijau yang lebih lembut, bersih, dan cocok untuk aplikasi kesehatan. Struktur ini siap dilanjutkan ke halaman login, master data, dan modul jadwal.
                    </p>
                </div>
            </div>
        </aside>
    </section>
@endsection
