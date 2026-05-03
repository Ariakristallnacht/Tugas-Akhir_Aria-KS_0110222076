<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Halaman login sistem penjadwalan layanan dan dinas luar Puskesmas Bunar.">
    <meta name="keywords" content="puskesmas, login, dashboard, layanan, dinas luar">
    <meta name="author" content="Puskesmas Bunar">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <title>Login | Puskesmas Bunar</title>

    <link rel="stylesheet" href="{{ asset('template/dist/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('template/dist/css/puskesmas-theme.css') }}">
</head>
<body class="pkm-login-page">
    <div class="page-loader bg-background fixed inset-0 z-[100] flex items-center justify-center transition-opacity">
        <div class="loader-spinner !w-14"></div>
    </div>

    <div class="pkm-login-shell relative h-screen overflow-hidden xl:bg-none before:hidden before:xl:block before:content-[''] before:w-[57%] before:-mt-[28%] before:-mb-[16%] before:-ml-[12%] before:absolute before:inset-y-0 before:left-0 before:transform before:rotate-[6deg] before:rounded-[35%] after:hidden after:xl:block after:content-[''] after:w-[57%] after:-mt-[28%] after:-mb-[16%] after:-ml-[12%] after:absolute after:inset-y-0 after:left-0 after:transform after:rotate-[6deg] after:border after:bg-cover after:blur-xl after:rounded-[35%] after:border-[20px]">
        <div class="pkm-login-glow p-3 sm:px-8 relative h-full before:hidden before:xl:block before:w-[57%] before:-mt-[20%] before:-mb-[13%] before:-ml-[12%] before:absolute before:inset-y-0 before:left-0 before:transform before:rotate-[-6deg] before:border before:opacity-60 before:rounded-[20%]">
            <div class="container relative z-10 mx-auto sm:px-20">
                <div class="block grid-cols-2 gap-4 xl:grid">
                    <div class="hidden min-h-screen flex-col xl:flex">
                        <a class="flex items-center pt-10" href="{{ url('/') }}">
                            <img class="w-6" src="{{ asset('template/dist/images/logo.svg') }}" alt="Puskesmas Bunar">
                            <span class="ml-3 text-xl font-medium text-white">
                                Puskesmas <span class="font-light opacity-70">Bunar Care</span>
                            </span>
                        </a>

                        <div class="my-auto">
                            <img class="-mt-16 w-1/2" src="{{ asset('template/dist/images/illustration.svg') }}" alt="Ilustrasi layanan kesehatan">
                            <div class="mt-10 text-4xl font-medium leading-tight text-white">
                                Kelola jadwal layanan dan <br>
                                dinas luar dalam satu sistem.
                            </div>
                            <div class="mt-5 max-w-xl text-lg text-white opacity-70">
                                Kelola jadwal, monitoring, verifikasi, dan laporan operasional Puskesmas Bunar dalam satu sistem.
                            </div>
                        </div>
                    </div>

                    <div class="my-10 flex h-screen py-5 xl:my-0 xl:h-auto xl:py-0">
                        <div class="pkm-login-card box relative p-5 before:absolute before:inset-0 before:mx-3 before:-mb-3 before:shadow-[0px_3px_5px_#0000000b] before:z-[-1] before:rounded-xl after:absolute after:inset-0 after:shadow-[0px_3px_5px_#0000000b] after:rounded-xl after:z-[-1] after:backdrop-blur-md mx-auto my-auto w-full px-5 py-8 sm:w-3/4 sm:px-8 lg:w-2/4 xl:ml-24 xl:w-auto xl:p-0 xl:before:hidden xl:after:hidden">
                            <h2 class="text-center text-2xl font-semibold xl:text-left xl:text-3xl">
                                Sign In
                            </h2>
                            <div class="mt-2 text-center opacity-70 xl:hidden">
                                Masuk ke sistem penjadwalan layanan dan dinas luar Puskesmas Bunar.
                            </div>

                            <form method="POST" action="{{ route('login.store') }}" class="mt-8 flex flex-col gap-5">
                                @csrf
                                <div>
                                    <input class="h-10 w-full rounded-md border bg-background ring-offset-background file:border-0 file:bg-transparent file:font-medium file:text-foreground placeholder:text-foreground/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-foreground/5 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 box block min-w-full px-5 py-6 xl:min-w-[28rem]" type="email" name="email" value="{{ old('email', 'admin@pkmbunar.test') }}" placeholder="Email" autocomplete="username" required autofocus>
                                    @error('email')
                                        <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <div class="relative">
                                        <input class="h-10 w-full rounded-md border bg-background ring-offset-background file:border-0 file:bg-transparent file:font-medium file:text-foreground placeholder:text-foreground/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-foreground/5 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 box block min-w-full px-5 py-6 pr-14 xl:min-w-[28rem]" type="password" name="password" value="password" placeholder="Password" autocomplete="current-password" required>
                                        <button type="button" class="password-toggle absolute inset-y-0 right-0 flex items-center justify-center px-4 opacity-70 transition hover:opacity-100" aria-label="Tampilkan password" aria-pressed="false">
                                            <svg class="password-toggle-icon-show size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M2.06 12.35a1 1 0 0 1 0-.7C3.52 7.64 7.27 5 12 5s8.48 2.64 9.94 6.65a1 1 0 0 1 0 .7C20.48 16.36 16.73 19 12 19s-8.48-2.64-9.94-6.65Z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                            <svg class="password-toggle-icon-hide hidden size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="m3 3 18 18"></path>
                                                <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"></path>
                                                <path d="M9.88 5.09A9.76 9.76 0 0 1 12 5c4.73 0 8.48 2.64 9.94 6.65a1 1 0 0 1 0 .7 10.46 10.46 0 0 1-4.24 5.1"></path>
                                                <path d="M6.61 6.61A10.45 10.45 0 0 0 2.06 11.65a1 1 0 0 0 0 .7C3.52 16.36 7.27 19 12 19a9.8 9.8 0 0 0 5.39-1.61"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    @error('password')
                                        <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex text-xs sm:text-sm">
                                    <div class="mr-auto flex items-center gap-2.5">
                                        <div class="bg-background border-foreground/70 relative size-4 rounded-sm border">
                                            <input class="peer relative z-10 size-full cursor-pointer opacity-0" type="checkbox" name="remember" value="1" id="remember-me" @checked(old('remember'))>
                                            <div class="z-4 bg-foreground invisible absolute inset-0 flex items-center justify-center text-white peer-checked:visible">
                                                <i data-lucide="check" class="size-4 stroke-[1.5]"></i>
                                            </div>
                                        </div>
                                        <label class="font-medium leading-none opacity-70" for="remember-me">Remember me</label>
                                    </div>
                                    <a class="opacity-70" href="#">Forgot Password?</a>
                                </div>

                                <div class="text-center xl:mt-2 xl:text-left">
                                    <button type="submit" class="cursor-pointer inline-flex border items-center justify-center gap-2 whitespace-nowrap rounded-lg text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-(--color)/20 border-(--color)/60 text-(--color) hover:bg-(--color)/5 [--color:var(--color-primary)] h-10 login-button box w-full px-4 py-5">
                                        Login
                                    </button>
                                </div>
                            </form>
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
