<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sistem Penjadwalan Puskesmas Bunar' }}</title>
    <meta name="description" content="Sistem penjadwalan layanan dan dinas luar Puskesmas Bunar.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('template/dist/css/themes/enigma/side-menu.css') }}">
    <link rel="stylesheet" href="{{ asset('template/dist/css/vendors/simplebar.css') }}">
    <link rel="stylesheet" href="{{ asset('template/dist/css/vendors/tiny-slider.css') }}">
    <link rel="stylesheet" href="{{ asset('template/dist/css/vendors/vector-map.css') }}">
    <link rel="stylesheet" href="{{ asset('template/dist/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('template/dist/css/puskesmas-theme.css') }}">
    @stack('styles')
</head>
<body class="font-['Plus_Jakarta_Sans']">
    <div class="page-loader bg-background fixed inset-0 z-[100] flex items-center justify-center transition-opacity">
        <div class="loader-spinner !w-14"></div>
    </div>

    <div class="enigma pkm-theme min-h-screen bg-[color-mix(in_oklch,_var(--color-background),_var(--color-foreground)_3%)] dark:bg-background before:bg-noise before:fixed before:inset-0 before:opacity-20 after:bg-accent after:bg-contain after:fixed after:inset-0 after:blur-xl after:opacity-[.18]">
        <div class="side-menu xl:ml-0 transition-[margin] duration-200 fixed top-0 left-0 z-50 group before:content-[''] before:fixed before:inset-0 before:bg-black/70 before:backdrop-blur before:xl:hidden after:content-[''] after:absolute after:inset-0 after:bg-background after:xl:hidden [&.side-menu--mobile-menu-open]:ml-0 -ml-[320px] before:hidden">
            <div class="close-mobile-menu fixed ml-[320px] xl:hidden z-50 cursor-pointer text-background [&.close-mobile-menu--mobile-menu-open]:block hidden">
                <div class="ml-5 mt-5 flex size-10 items-center justify-center">
                    <i data-lucide="x" class="size-7 stroke-1"></i>
                </div>
            </div>

            <div class="side-menu__content relative z-20 flex h-screen w-[320px] flex-col overflow-hidden">
                <div class="relative z-10 hidden h-[92px] flex-none items-center px-8 xl:flex">
                    <a class="flex items-center gap-3" href="{{ route('dashboard') }}">
                        <div class="pkm-logo-shell flex size-11 items-center justify-center rounded-2xl">
                            <i data-lucide="heart-pulse" class="size-5"></i>
                        </div>
                        <div class="text-background">
                            <div class="text-sm font-medium uppercase tracking-[0.2em] text-white/70">Puskesmas</div>
                            <div class="text-lg font-semibold text-white">Bunar Care</div>
                        </div>
                    </a>
                </div>

                <div class="relative mx-5 mb-2 mt-2 rounded-[28px] border border-white/10 bg-white/10 p-4 text-white backdrop-blur xl:mx-8">
                    <div class="flex items-center gap-3">
                        <div class="flex size-12 items-center justify-center rounded-2xl bg-white/15">
                            <i data-lucide="stethoscope" class="size-5"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold">Admin Puskesmas</div>
                            <div class="truncate text-xs text-white/70">Pengelola penjadwalan layanan</div>
                        </div>
                    </div>
                </div>

                <div class="h-full overflow-y-auto px-4 pb-6 pt-3 xl:px-7">
                    <ul class="scrollable flex flex-col gap-1.5">
                        <li class="side-menu__group-label">Menu Utama</li>
                        <li>
                            <a href="{{ route('dashboard') }}" class="side-menu__link {{ request()->routeIs('dashboard') ? 'side-menu__link--active' : '' }}">
                                <i data-lucide="layout-dashboard" class="side-menu__link__icon size-4"></i>
                                <div class="side-menu__link__title">Dashboard</div>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="side-menu__link">
                                <i data-lucide="calendar-range" class="side-menu__link__icon size-4"></i>
                                <div class="side-menu__link__title">Jadwal Layanan</div>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="side-menu__link">
                                <i data-lucide="briefcase-medical" class="side-menu__link__icon size-4"></i>
                                <div class="side-menu__link__title">Dinas Luar</div>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="side-menu__link">
                                <i data-lucide="users" class="side-menu__link__icon size-4"></i>
                                <div class="side-menu__link__title">Data Pegawai</div>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="side-menu__link">
                                <i data-lucide="file-text" class="side-menu__link__icon size-4"></i>
                                <div class="side-menu__link__title">Laporan Kegiatan</div>
                            </a>
                        </li>
                        <li class="side-menu__group-label mt-6">Monitoring</li>
                        <li>
                            <a href="#" class="side-menu__link">
                                <i data-lucide="activity" class="side-menu__link__icon size-4"></i>
                                <div class="side-menu__link__title">Monitoring Harian</div>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="side-menu__link">
                                <i data-lucide="shield-check" class="side-menu__link__icon size-4"></i>
                                <div class="side-menu__link__title">Verifikasi Dinas</div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="content xl:ml-[320px] px-4 pb-10 pt-4 xl:px-8 xl:pt-6">
            <header class="pkm-topbar mb-6 flex flex-col gap-4 rounded-[30px] px-5 py-5 xl:flex-row xl:items-center xl:justify-between xl:px-7">
                <div class="flex items-center gap-3">
                    <a href="javascript:;" class="mobile-menu-toggler flex size-11 items-center justify-center rounded-2xl border border-slate-200/80 bg-white xl:hidden">
                        <i data-lucide="menu" class="size-5"></i>
                    </a>
                    <div>
                        <div class="text-sm font-medium text-emerald-700/80">Sistem Penjadwalan dan Dinas Luar</div>
                        <h1 class="text-2xl font-semibold text-slate-800">{{ $heading ?? 'Dashboard Puskesmas Bunar' }}</h1>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="pkm-search flex items-center gap-3 rounded-2xl px-4 py-3">
                        <i data-lucide="search" class="size-4 text-slate-400"></i>
                        <input type="text" placeholder="Cari jadwal, pegawai, atau layanan..." class="w-full bg-transparent text-sm outline-none placeholder:text-slate-400 sm:w-72">
                    </div>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100">
                        <i data-lucide="log-in" class="size-4"></i>
                        Halaman Login
                    </a>
                </div>
            </header>

            @yield('content')
        </div>
    </div>

    <script src="{{ asset('template/dist/js/vendors/dom.js') }}"></script>
    <script src="{{ asset('template/dist/js/vendors/lucide.js') }}"></script>
    <script src="{{ asset('template/dist/js/vendors/dropdown.js') }}"></script>
    <script src="{{ asset('template/dist/js/vendors/modal.js') }}"></script>
    <script src="{{ asset('template/dist/js/vendors/simplebar.js') }}"></script>
    <script src="{{ asset('template/dist/js/components/base/page-loader.js') }}"></script>
    <script src="{{ asset('template/dist/js/components/base/lucide.js') }}"></script>
    <script src="{{ asset('template/dist/js/components/theme-switcher.js') }}"></script>
    <script src="{{ asset('template/dist/js/themes/enigma.js') }}"></script>
    @stack('scripts')
</body>
</html>
