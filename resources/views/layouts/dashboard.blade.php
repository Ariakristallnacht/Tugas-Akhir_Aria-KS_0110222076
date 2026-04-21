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
        $dashboardHeading = match ($roleCode) {
            'admin' => 'Dashboard Admin',
            'pj_penjadwalan' => 'Dashboard PJ Penjadwalan',
            'pegawai' => 'Dashboard Pegawai',
            default => 'Dashboard',
        };
    @endphp
    <div class="page-loader bg-background fixed inset-0 z-[100] flex items-center justify-center transition-opacity">
        <div class="loader-spinner !w-14"></div>
    </div>

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
                        <div class="pkm-sidebar__profile-name">{{ $user?->name ?? 'Pengguna' }}</div>
                        <div class="pkm-sidebar__profile-role">{{ $roleLabel }}</div>
                    </div>
                </div>

                <nav class="pkm-nav">
                    <div class="pkm-nav__group">
                        <div class="pkm-nav__label">Dashboard</div>
                        <a href="{{ auth()->user()->dashboardPath() }}" class="pkm-nav__item {{ request()->routeIs('admin.dashboard', 'pj.dashboard', 'pegawai.dashboard') ? 'is-active' : '' }}">
                            <span class="pkm-nav__icon"><i data-lucide="layout-dashboard" class="size-4"></i></span>
                            <span>Dashboard</span>
                            <span class="pkm-nav__badge">Home</span>
                        </a>
                    </div>

                    @if ($roleCode === 'admin')
                        <div class="pkm-nav__group">
                            <div class="pkm-nav__label">Manajemen</div>
                            <a href="{{ route('admin.monitoring-jadwal') }}" class="pkm-nav__item {{ request()->routeIs('admin.monitoring-jadwal') ? 'is-active' : '' }}">
                                <span class="pkm-nav__icon"><i data-lucide="calendar-range" class="size-4"></i></span>
                                <span>Monitoring Jadwal</span>
                            </a>
                            <a href="{{ route('admin.monitoring-laporan') }}" class="pkm-nav__item {{ request()->routeIs('admin.monitoring-laporan*') ? 'is-active' : '' }}">
                                <span class="pkm-nav__icon"><i data-lucide="file-spreadsheet" class="size-4"></i></span>
                                <span>Monitoring Laporan</span>
                            </a>
                            <a href="{{ route('admin.pegawai.index') }}" class="pkm-nav__item {{ request()->routeIs('admin.pegawai.*') ? 'is-active' : '' }}">
                                <span class="pkm-nav__icon"><i data-lucide="briefcase-business" class="size-4"></i></span>
                                <span>Kelola Pegawai</span>
                            </a>
                        </div>
                    @elseif ($roleCode === 'pj_penjadwalan')
                        <div class="pkm-nav__group">
                            <div class="pkm-nav__label">Operasional</div>
                            <div class="pkm-nav__submenu">
                                <a href="#" class="pkm-nav__subitem is-active">
                                    <i data-lucide="calendar-days" class="size-4"></i>
                                    <span>Jadwal Layanan</span>
                                </a>
                                <a href="#" class="pkm-nav__subitem">
                                    <i data-lucide="shield-check" class="size-4"></i>
                                    <span>Verifikasi Dinas</span>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="pkm-nav__group">
                            <div class="pkm-nav__label">Aktivitas</div>
                            <div class="pkm-nav__submenu">
                                <a href="{{ route('pegawai.dashboard') }}" class="pkm-nav__subitem {{ request()->routeIs('pegawai.dashboard') ? 'is-active' : '' }}">
                                    <i data-lucide="calendar-range" class="size-4"></i>
                                    <span>Jadwal Saya</span>
                                </a>
                                <a href="{{ route('pegawai.pengajuan-dinas.index') }}" class="pkm-nav__subitem {{ request()->routeIs('pegawai.pengajuan-dinas.*') ? 'is-active' : '' }}">
                                    <i data-lucide="briefcase-business" class="size-4"></i>
                                    <span>Pengajuan Dinas</span>
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
            <button class="pkm-sidebar-overlay js-pkm-sidebar-close" type="button" aria-label="Tutup sidebar"></button>

            <main class="pkm-main">
                <header class="pkm-topbar">
                    <div class="pkm-topbar__headline">
                        <button class="pkm-mobile-menu js-pkm-sidebar-open" type="button" aria-label="Buka menu">
                            <i data-lucide="menu" class="size-5"></i>
                        </button>
                        <div class="pkm-breadcrumb">
                            <span>Apps</span>
                            <i data-lucide="chevron-right" class="size-4"></i>
                            <span>{{ $roleLabel }}</span>
                            <i data-lucide="chevron-right" class="size-4"></i>
                            <span>{{ $heading ?? $dashboardHeading }}</span>
                        </div>
                        <h1 class="pkm-main__title">{{ $heading ?? $dashboardHeading }}</h1>
                    </div>

                    <div class="pkm-topbar__actions">
                        <div class="pkm-topbar__search">
                            <i data-lucide="search" class="size-4 text-slate-400"></i>
                            <input type="text" placeholder="Cari jadwal, pegawai, atau layanan...">
                        </div>
                        <button class="pkm-topbar__icon" type="button">
                            <i data-lucide="bell" class="size-4"></i>
                        </button>
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
            const openButtons = document.querySelectorAll('.js-pkm-sidebar-open');
            const closeButtons = document.querySelectorAll('.js-pkm-sidebar-close');

            if (!shell) {
                return;
            }

            const openSidebar = () => shell.classList.add('pkm-shell--sidebar-open');
            const closeSidebar = () => shell.classList.remove('pkm-shell--sidebar-open');

            openButtons.forEach((button) => button.addEventListener('click', openSidebar));
            closeButtons.forEach((button) => button.addEventListener('click', closeSidebar));

            window.addEventListener('resize', () => {
                if (window.innerWidth > 1280) {
                    closeSidebar();
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
