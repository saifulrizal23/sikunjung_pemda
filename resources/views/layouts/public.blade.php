<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#174565">
    <title>@yield('title', config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --ink: #18334b;
            --navy: #174565;
            --teal: #087e83;
            --muted: #74869a;
            --mist: #f3f7fa;
        }

        body { min-width: 320px; min-height: 100vh; display: flex; flex-direction: column; color: var(--ink); background: var(--mist); }
        a { text-decoration: none; }
        .public-header { position: sticky; z-index: 1020; top: 0; border-bottom: 1px solid #e6edf2; background: rgb(255 255 255 / 94%); backdrop-filter: blur(12px); }
        .public-nav { min-height: 76px; }
        .brand-mark { display: grid; width: 42px; height: 42px; flex: 0 0 42px; place-items: center; border-radius: 14px; color: #fff; background: linear-gradient(145deg, #24a6a2, #087e83); font-size: .9rem; font-weight: 800; box-shadow: 0 7px 16px rgb(0 0 0 / 15%); }
        .brand-name { color: var(--navy); font-size: .98rem; font-weight: 750; line-height: 1.25; }
        .brand-caption { margin-top: .18rem; color: #74869a; font-size: .69rem; letter-spacing: .04em; }
        .public-nav-link { color: #52697c; font-size: .9rem; font-weight: 600; }
        .public-nav-link:hover, .public-nav-link.active { color: var(--teal); }
        .public-main { width: min(100%, 1480px); flex: 1; margin: 0 auto; padding: clamp(1.25rem, 3vw, 2.5rem); }
        .public-footer { padding: 1rem clamp(1rem, 3vw, 2.5rem); border-top: 1px solid #e6edf2; color: #8392a1; font-size: .75rem; }
        .hero-panel { position: relative; overflow: hidden; border-radius: 1.4rem; color: #fff; background: linear-gradient(120deg, #102f4f, #087e83); }
        .hero-panel::after { position: absolute; right: -5rem; bottom: -11rem; width: 25rem; height: 25rem; border: 1px solid rgb(255 255 255 / 15%); border-radius: 50%; content: ""; }
        .hero-panel > * { position: relative; z-index: 1; }
        .surface { border: 1px solid #e4edf2; border-radius: 1rem; background: #fff; box-shadow: 0 12px 32px rgb(21 50 75 / 5%); }
        .eyebrow { color: var(--teal); font-size: .76rem; font-weight: 700; letter-spacing: .13em; text-transform: uppercase; }
        .stat-value { color: var(--navy); font-size: 2rem; font-weight: 750; line-height: 1; }
        .stat-mark { width: .55rem; height: 2.4rem; border-radius: 1rem; background: var(--teal); }
        .section-title { font-weight: 700; letter-spacing: -.025em; }
        .table > :not(caption) > * > * { padding: .9rem .8rem; }
        .btn-teal { border-color: var(--teal); color: #fff; background: var(--teal); }
        .btn-teal:hover, .btn-teal:focus { border-color: #06676b; color: #fff; background: #06676b; }
        .form-control { min-height: 48px; border-color: #d6e1e9; border-radius: .7rem; }
        .form-control:focus { border-color: #64b9b6; box-shadow: 0 0 0 .2rem rgb(8 126 131 / 13%); }
        .login-main { display: grid; flex: 1; place-items: center; padding: 2rem 1rem; }
        .login-card { width: min(100%, 460px); padding: clamp(1.5rem, 5vw, 2.5rem); border: 1px solid #e4edf2; border-radius: 1.25rem; background: #fff; box-shadow: 0 22px 65px rgb(21 50 75 / 9%); }
        .login-card .form-control { background: #f9fbfc; }

        @media (max-width: 575.98px) {
            .public-nav { min-height: 68px; }
            .public-main { padding: 1.1rem 1rem 1.5rem; }
            .hero-panel { border-radius: 1rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="public-header">
        <nav class="container public-nav d-flex align-items-center justify-content-between gap-3" aria-label="Navigasi publik">
            <a class="d-inline-flex align-items-center gap-3" href="{{ route('home') }}">
                <span class="brand-mark" aria-hidden="true">SK</span>
                <span>
                    <span class="brand-name d-block">SI Kunjung Pemda</span>
                    <span class="brand-caption d-block">LAYANAN KUNJUNGAN</span>
                </span>
            </a>
            @if (request()->routeIs('login'))
                <a class="public-nav-link" href="{{ route('home') }}">Kembali ke beranda</a>
            @else
                <div class="d-flex align-items-center justify-content-end gap-3 gap-md-4">
                    <a class="public-nav-link d-none d-sm-inline" href="{{ route('jadwal') }}">Jadwal layanan</a>
                    <a class="public-nav-link d-none d-sm-inline" href="{{ route('struktur') }}">Struktur karyawan</a>
                    @auth
                        <a class="btn btn-teal rounded-pill px-3" href="{{ route('dashboard') }}">Dashboard</a>
                    @else
                        <a class="btn btn-teal rounded-pill px-3" href="{{ route('login') }}">Masuk</a>
                    @endauth
                </div>
            @endif
        </nav>
    </header>

    @if (request()->routeIs('login'))
        <main class="login-main">
            @if (session('status'))
                <div class="alert alert-success border-0 shadow-sm position-absolute top-0 mt-3" role="status">{{ session('status') }}</div>
            @endif
            @yield('content')
        </main>
    @else
        <main class="public-main">
            @if (session('status'))
                <div class="alert alert-success border-0 shadow-sm" role="status">{{ session('status') }}</div>
            @endif
            @yield('content')
        </main>
    @endif

    <footer class="public-footer">
        <div class="container">&copy; {{ now()->year }} SI Kunjung Pemda <span class="px-1">·</span> Layanan informasi kunjungan</div>
    </footer>
</body>
</html>
