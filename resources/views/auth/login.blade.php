<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | Puskesmas Bunar</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('template/dist/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('template/dist/css/puskesmas-theme.css') }}">
</head>
<body class="font-['Plus_Jakarta_Sans']">
    <div class="page-loader bg-background fixed inset-0 z-[100] flex items-center justify-center transition-opacity">
        <div class="loader-spinner !w-14"></div>
    </div>

    <div class="pkm-login-shell relative min-h-screen overflow-hidden bg-emerald-950">
        <div class="pkm-login-glow"></div>
        <div class="container relative z-10 mx-auto px-4 py-6 sm:px-10">
            <div class="grid min-h-screen gap-6 xl:grid-cols-2 xl:gap-10">
                <div class="hidden xl:flex xl:flex-col xl:justify-between xl:py-8">
                    <a class="flex items-center gap-3" href="{{ url('/') }}">
                        <div class="pkm-logo-shell flex size-12 items-center justify-center rounded-2xl">
                            <i data-lucide="heart-pulse" class="size-5"></i>
                        </div>
                        <div class="text-white">
                            <div class="text-sm uppercase tracking-[0.22em] text-white/65">Sistem Penjadwalan</div>
                            <div class="text-xl font-semibold">Puskesmas Bunar</div>
                        </div>
                    </a>

                    <div class="max-w-xl">
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/75 backdrop-blur">
                            <i data-lucide="hospital" class="size-3.5"></i>
                            Layanan Kesehatan Terintegrasi
                        </div>
                        <h1 class="mt-6 text-5xl font-semibold leading-tight text-white">Masuk ke sistem layanan dan dinas luar dengan tampilan yang lebih hangat dan profesional.</h1>
                        <p class="mt-6 text-lg leading-8 text-white/72">
                            Halaman login ini dibangun dari aset template di `public/template` lalu disesuaikan menjadi nuansa hijau lembut agar lebih selaras dengan karakter aplikasi kesehatan.
                        </p>
                    </div>

                    <div class="rounded-[32px] border border-white/10 bg-white/8 p-6 text-white backdrop-blur">
                        <div class="grid grid-cols-3 gap-4">
                            <div><div class="text-3xl font-semibold">48</div><div class="mt-1 text-sm text-white/70">Pegawai aktif</div></div>
                            <div><div class="text-3xl font-semibold">12</div><div class="mt-1 text-sm text-white/70">Jadwal hari ini</div></div>
                            <div><div class="text-3xl font-semibold">4</div><div class="mt-1 text-sm text-white/70">Dinas luar</div></div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-center py-6 xl:py-10">
                    <div class="pkm-login-card w-full max-w-[34rem] rounded-[34px] p-6 sm:p-8">
                        <div class="mb-8 xl:hidden">
                            <a class="flex items-center gap-3" href="{{ url('/') }}">
                                <div class="pkm-logo-shell flex size-11 items-center justify-center rounded-2xl">
                                    <i data-lucide="heart-pulse" class="size-5"></i>
                                </div>
                                <div>
                                    <div class="text-sm uppercase tracking-[0.22em] text-emerald-700/70">Sistem Penjadwalan</div>
                                    <div class="text-lg font-semibold text-slate-800">Puskesmas Bunar</div>
                                </div>
                            </a>
                        </div>

                        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.15em] text-emerald-700">
                            <i data-lucide="shield-check" class="size-3.5"></i>
                            Akses Pengguna
                        </div>
                        <h2 class="mt-5 text-3xl font-semibold text-slate-800">Masuk ke akun Anda</h2>
                        <p class="mt-3 text-sm leading-7 text-slate-500">
                            Gunakan email dan kata sandi untuk mengakses dashboard penjadwalan layanan, dinas luar, dan laporan kegiatan.
                        </p>

                        <form method="POST" action="{{ route('login.store') }}" class="mt-8 flex flex-col gap-5">
                            @csrf
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                                <input type="email" name="email" value="{{ old('email', 'admin@pkmbunar.test') }}" class="pkm-input" placeholder="masukkan email" required autofocus>
                                @error('email')
                                    <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">Kata sandi</label>
                                <input type="password" name="password" value="password" class="pkm-input" placeholder="masukkan kata sandi" required>
                                @error('password')
                                    <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-center justify-between gap-4 text-sm">
                                <label class="flex items-center gap-2 text-slate-500">
                                    <input type="checkbox" name="remember" value="1" class="rounded border-emerald-200 text-emerald-600 focus:ring-emerald-500" @checked(old('remember'))>
                                    Ingat saya
                                </label>
                                <a href="#" class="font-medium text-emerald-700">Lupa kata sandi?</a>
                            </div>
                            <button type="submit" class="pkm-primary-button">
                                <i data-lucide="log-in" class="size-4"></i>
                                Masuk ke Dashboard
                            </button>
                        </form>

                        <div class="mt-8 rounded-[24px] bg-emerald-50/80 p-4 text-sm leading-7 text-emerald-800/80">
                            Akun contoh: `admin@pkmbunar.test`, `pj@pkmbunar.test`, atau `pegawai@pkmbunar.test` dengan kata sandi `password`.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('template/dist/js/vendors/dom.js') }}"></script>
    <script src="{{ asset('template/dist/js/vendors/lucide.js') }}"></script>
    <script src="{{ asset('template/dist/js/pages/login.js') }}"></script>
    <script src="{{ asset('template/dist/js/components/base/page-loader.js') }}"></script>
    <script src="{{ asset('template/dist/js/components/base/lucide.js') }}"></script>
</body>
</html>
