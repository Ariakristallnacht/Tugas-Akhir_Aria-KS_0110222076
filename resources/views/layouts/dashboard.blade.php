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

    <link rel="stylesheet" href="{{ asset('template/dist/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('template/dist/css/puskesmas-theme.css') }}">
    @stack('styles')
</head>
<body class="font-['Plus_Jakarta_Sans']">
    @php
        $user = auth()->user();
        $roleCode = $user?->roleKode();
        $roleLabel = $user?->role?->nama ?? 'Pengguna';
        $profileName = match ($roleCode) {
            'admin' => 'Admin',
            'pj_penjadwalan' => 'PJ Penjadwalan',
            default => $user?->name ?? 'Pengguna',
        };
        $dashboardHeading = match ($roleCode) {
            'admin' => 'Dashboard Admin',
            'pj_penjadwalan' => 'Dashboard PJ Penjadwalan',
            'pegawai' => 'Dashboard Pegawai',
            default => 'Dashboard',
        };
    @endphp
    <!-- Page-loader di-disable secara default menggunakan display:none inline style agar dijamin 100% tidak pernah mengunci layar pengguna jika JS telat diproses -->
    <div class="page-loader bg-background fixed inset-0 z-[100] flex items-center justify-center transition-opacity" id="app-page-loader" style="display: none; opacity: 0; pointer-events: none;">
        <div class="loader-spinner !w-14"></div>
    </div>
    <script>
        // Sembunyikan loader secara instan jika JS utama butuh waktu memproses
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var loader = document.getElementById('app-page-loader');
                if (loader) {
                    loader.style.opacity = '0';
                    loader.style.pointerEvents = 'none';
                    setTimeout(function() {
                        loader.style.display = 'none';
                    }, 300);
                }
            }, 50);
        });
    </script>

    <div class="pkm-app-shell">
        <div class="pkm-app-backdrop"></div>

        <div class="pkm-shell">
            <aside class="pkm-sidebar">
                <div class="pkm-sidebar__brand">
                    <a class="pkm-brand" href="{{ auth()->check() ? auth()->user()->dashboardPath() : url('/') }}">
                        <span class="pkm-brand__badge">
                            <i data-lucide="heart-pulse" class="size-5"></i>
                        </span>
                        <span>
                            <span class="pkm-brand__eyebrow">Puskesmas</span>
                            <span class="pkm-brand__title">Bunar Care</span>
                        </span>
                    </a>
                    <button class="pkm-sidebar__collapse js-pkm-sidebar-close" type="button" aria-label="Tutup menu">
                        <i data-lucide="chevron-left" class="size-4"></i>
                    </button>
                </div>

                <div class="pkm-sidebar__profile">
                    <div class="pkm-sidebar__profile-icon">
                        <i data-lucide="stethoscope" class="size-5"></i>
                    </div>
                    <div>
                        <div class="pkm-sidebar__profile-name">{{ $profileName }}</div>
                        <div class="pkm-sidebar__profile-role">{{ $roleLabel }}</div>
                    </div>
                </div>

                <nav class="pkm-nav">
                    <div class="pkm-nav__group">
                        <div class="pkm-nav__label">Dashboard</div>
                        <a href="{{ auth()->user()->dashboardPath() }}" class="pkm-nav__item {{ request()->routeIs('admin.dashboard', 'pj.dashboard', 'pegawai.dashboard') ? 'is-active' : '' }}">
                            <span class="pkm-nav__icon"><i data-lucide="layout-dashboard" class="size-4"></i></span>
                            <span>Dashboard</span>
                        </a>
                    </div>

                    @if ($roleCode === 'admin')
                        <div class="pkm-nav__group">
                            <div class="pkm-nav__label">Manajemen</div>
                            <div class="pkm-nav__submenu">
                                <a href="{{ route('admin.monitoring-jadwal') }}" class="pkm-nav__subitem {{ request()->routeIs('admin.monitoring-jadwal') ? 'is-active' : '' }}">
                                    <i data-lucide="calendar-range" class="size-4"></i>
                                    <span>Monitoring Jadwal Kegiatan</span>
                                </a>
                                <a href="{{ route('admin.pegawai.index') }}" class="pkm-nav__subitem {{ request()->routeIs('admin.pegawai.*') ? 'is-active' : '' }}">
                                    <i data-lucide="briefcase-business" class="size-4"></i>
                                    <span>Kelola Pegawai</span>
                                </a>
                                <a href="{{ route('admin.monitoring-laporan') }}" class="pkm-nav__subitem {{ request()->routeIs('admin.monitoring-laporan*') ? 'is-active' : '' }}">
                                    <i data-lucide="file-spreadsheet" class="size-4"></i>
                                    <span>Monitoring Laporan Kegiatan</span>
                                </a>
                            </div>
                        </div>
                    @elseif ($roleCode === 'pj_penjadwalan')
                        <div class="pkm-nav__group">
                            <div class="pkm-nav__label">Manajemen</div>
                            <div class="pkm-nav__submenu">
                                <a href="{{ route('pj.jadwal-kegiatan.index') }}" class="pkm-nav__subitem {{ request()->routeIs('pj.jadwal-kegiatan.*') ? 'is-active' : '' }}">
                                    <i data-lucide="calendar-plus" class="size-4"></i>
                                    <span>Kelola Jadwal Kegiatan</span>
                                </a>
                                <a href="{{ route('pj.verifikasi-pengajuan-dinas.index') }}" class="pkm-nav__subitem {{ request()->routeIs('pj.verifikasi-pengajuan-dinas.*') ? 'is-active' : '' }}">
                                    <i data-lucide="shield-check" class="size-4"></i>
                                    <span>Verifikasi Dinas Luar</span>
                                </a>
                                <a href="{{ route('pj.kegiatan.index') }}" class="pkm-nav__subitem {{ request()->routeIs('pj.kegiatan.*') ? 'is-active' : '' }}">
                                    <i data-lucide="folders" class="size-4"></i>
                                    <span>Kelola Layanan</span>
                                </a>
                                 <a href="{{ route('pj.monitoring-laporan') }}" class="pkm-nav__subitem {{ request()->routeIs('pj.monitoring-laporan*') ? 'is-active' : '' }}">
                                    <i data-lucide="file-spreadsheet" class="size-4"></i>
                                    <span>Monitoring Laporan Kegiatan</span>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="pkm-nav__group">
                            <div class="pkm-nav__label">Manajemen</div>
                            <div class="pkm-nav__submenu">
                                <a href="{{ route('pegawai.jadwal-kegiatan') }}" class="pkm-nav__subitem {{ request()->routeIs('pegawai.jadwal-kegiatan') ? 'is-active' : '' }}">
                                    <i data-lucide="calendar-range" class="size-4"></i>
                                    <span>Jadwal Kegiatan</span>
                                </a>
                                <a href="{{ route('pegawai.pengajuan-dinas.index') }}" class="pkm-nav__subitem {{ request()->routeIs('pegawai.pengajuan-dinas.*') ? 'is-active' : '' }}">
                                    <i data-lucide="briefcase-business" class="size-4"></i>
                                    <span>Pengajuan Dinas Luar</span>
                                </a>
                                <a href="{{ route('pegawai.laporan-kegiatan.index') }}" class="pkm-nav__subitem {{ request()->routeIs('pegawai.laporan-kegiatan.*') ? 'is-active' : '' }}">
                                    <i data-lucide="file-pen-line" class="size-4"></i>
                                    <span>Laporan Kegiatan</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </nav>

                <div class="pkm-sidebar__footer">
                    <div class="pkm-sidebar__footer-avatar">{{ strtoupper(substr($user?->name ?? 'AB', 0, 1)) }}</div>
                    <div class="min-w-0">
                        <div class="truncate font-semibold text-white">{{ $user?->name ?? 'Pengguna' }}</div>
                        <div class="truncate text-sm text-white/60">{{ $roleLabel }}</div>
                    </div>
                    <i data-lucide="move-right" class="size-4 text-white/55"></i>
                </div>
            </aside>
            <button class="pkm-sidebar-overlay js-pkm-sidebar-close" type="button" aria-label="Tutup sidebar" style="display: none; pointer-events: none;" aria-hidden="true"></button>

            <main class="pkm-main">
                <header class="pkm-topbar">
                    <div class="pkm-topbar__headline">
                        <button class="pkm-mobile-menu js-pkm-sidebar-open" type="button" aria-label="Buka menu">
                            <i data-lucide="menu" class="size-5"></i>
                        </button>
                        <div class="pkm-breadcrumb" aria-label="Breadcrumb">
                            <span class="pkm-breadcrumb__item">Apps</span>
                            <i data-lucide="chevron-right" class="size-4"></i>
                            <span class="pkm-breadcrumb__item">{{ $roleLabel }}</span>
                            <i data-lucide="chevron-right" class="size-4"></i>
                            <span class="pkm-breadcrumb__item is-current">{{ $heading ?? $dashboardHeading }}</span>
                        </div>
                    </div>

                    <div class="pkm-topbar__actions">
                        <form method="GET" action="{{ route('search') }}" class="pkm-topbar__search">
                            <i data-lucide="search" class="size-4 text-slate-400"></i>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari jadwal, pegawai, atau layanan...">
                        </form>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="pkm-topbar__login">Logout</button>
                        </form>
                        <div class="pkm-topbar__avatar">{{ strtoupper(substr(auth()->user()->name ?? 'AB', 0, 2)) }}</div>
                    </div>
                </header>

                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('template/dist/js/vendors/dom.js') }}"></script>
    <script src="{{ asset('template/dist/js/vendors/lucide.js') }}"></script>
    <script src="{{ asset('template/dist/js/components/base/page-loader.js') }}"></script>
    <script src="{{ asset('template/dist/js/components/base/lucide.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const shell = document.querySelector('.pkm-shell');
            const sidebarOverlay = document.querySelector('.pkm-sidebar-overlay');
            const openButtons = document.querySelectorAll('.js-pkm-sidebar-open');
            const closeButtons = document.querySelectorAll('.js-pkm-sidebar-close');

            if (!shell) {
                return;
            }

            const isMobileSidebar = () => window.innerWidth <= 1280;

            const syncSidebarOverlay = () => {
                if (!sidebarOverlay) {
                    return;
                }

                const sidebarOpen = shell.classList.contains('pkm-shell--sidebar-open');
                const shouldShowOverlay = isMobileSidebar() && sidebarOpen;

                sidebarOverlay.style.display = shouldShowOverlay ? 'block' : 'none';
                sidebarOverlay.style.pointerEvents = shouldShowOverlay ? 'auto' : 'none';
                sidebarOverlay.setAttribute('aria-hidden', shouldShowOverlay ? 'false' : 'true');
            };

            const openSidebar = () => {
                if (!isMobileSidebar()) {
                    syncSidebarOverlay();
                    return;
                }

                shell.classList.add('pkm-shell--sidebar-open');
                syncSidebarOverlay();
            };

            const closeSidebar = () => {
                shell.classList.remove('pkm-shell--sidebar-open');
                syncSidebarOverlay();
            };

            openButtons.forEach((button) => button.addEventListener('click', openSidebar));
            closeButtons.forEach((button) => button.addEventListener('click', closeSidebar));

            window.addEventListener('resize', syncSidebarOverlay);
            window.addEventListener('pageshow', syncSidebarOverlay);

            syncSidebarOverlay();
        });
    </script>
    @stack('scripts')
</body>
</html>
