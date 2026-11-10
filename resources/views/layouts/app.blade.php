<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#102f4f">
    <title>@yield('title', config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --ink: #18334b;
            --navy: #174565;
            --teal: #087e83;
            --muted: #74869a;
            --mist: #f4f7fa;
            --sidebar-width: 272px;
        }

        body { min-width: 320px; min-height: 100vh; color: var(--ink); background: var(--mist); }
        a { text-decoration: none; }
        .app-sidebar {
            --bs-offcanvas-width: var(--sidebar-width);
            z-index: 1045;
            width: var(--sidebar-width);
            color: #edf6fc;
            background: linear-gradient(180deg, #205b86 0%, #174565 100%);
            border: 0;
        }
        .app-sidebar .offcanvas-body { display: flex; flex-direction: column; padding: 0; }
        .sidebar-inner { display: flex; min-height: 100%; flex: 1; flex-direction: column; }
        .sidebar-brand { display: flex; min-height: 88px; align-items: center; gap: .8rem; padding: 1.25rem 1.35rem; border-bottom: 1px solid rgb(255 255 255 / 9%); }
        .brand-mark {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 14px;
            color: #fff;
            background: linear-gradient(145deg, #24a6a2, #087e83);
            font-size: .9rem;
            font-weight: 800;
            box-shadow: 0 7px 16px rgb(0 0 0 / 15%);
        }
        .brand-name { color: #fff; font-size: .98rem; font-weight: 750; line-height: 1.25; }
        .brand-caption { margin-top: .18rem; color: #c1d9e9; font-size: .69rem; letter-spacing: .04em; }
        .sidebar-close { filter: invert(1); opacity: .8; }
        .profile-card { display: flex; align-items: center; gap: .7rem; margin: 1.15rem 1rem .7rem; padding: .8rem; border: 1px solid #d5dce2; border-radius: 13px; color: #263d50; background: linear-gradient(135deg, #f1f3f5 0%, #d7dde2 100%); box-shadow: 0 8px 18px rgb(5 32 54 / 12%); }
        .profile-avatar { display: grid; width: 38px; height: 38px; flex: 0 0 38px; place-items: center; border: 1px solid rgb(255 255 255 / 55%); border-radius: 50%; color: #fff; background: linear-gradient(145deg, #718293, #46596c); font-size: .8rem; font-weight: 700; }
        .profile-name { overflow: hidden; color: #263d50; font-size: .82rem; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
        .profile-role { color: #647789; font-size: .7rem; }
        .sidebar-nav { flex: 1; padding: .6rem .9rem 1.1rem; }
        .nav-caption { padding: .85rem .65rem .45rem; color: #c1d9e9; font-size: .65rem; font-weight: 750; letter-spacing: .13em; text-transform: uppercase; }
        .sidebar-link {
            display: flex;
            min-height: 45px;
            align-items: center;
            gap: .78rem;
            margin: .15rem 0;
            padding: .65rem .75rem;
            border: 1px solid transparent;
            border-radius: 10px;
            color: #e1edf6;
            font-size: .86rem;
            font-weight: 550;
            transition: background-color .18s ease, color .18s ease, border-color .18s ease;
        }
        .sidebar-link:hover { color: #fff; background: rgb(255 255 255 / 13%); }
        .sidebar-link.active { border-color: rgb(161 255 231 / 48%); color: #fff; background: linear-gradient(100deg, #0d9293, #147b91); box-shadow: inset 4px 0 #a1ffe7, 0 5px 16px rgb(5 32 54 / 18%); }
        .sidebar-link svg { width: 19px; height: 19px; flex: 0 0 19px; color: #c5dbea; }
        .sidebar-link.active svg { color: #c8fff0; }
        .sidebar-footer { padding: .9rem 1rem 1.2rem; border-top: 1px solid rgb(255 255 255 / 15%); }
        .sidebar-help { margin-bottom: .75rem; padding: .8rem; border-radius: 11px; color: #d6e7f2; background: rgb(255 255 255 / 9%); font-size: .72rem; line-height: 1.55; }
        .sidebar-logout { width: 100%; border-color: #cbd4dc; color: #334b5f; background: linear-gradient(135deg, #eef1f4, #d6dde3); text-align: left; }
        .sidebar-logout:hover, .sidebar-logout:focus-visible { border-color: #aebbc6; color: #1d354a; background: linear-gradient(135deg, #e1e6ea, #c8d1d9); }
        .app-main { min-width: 0; min-height: 100vh; margin-left: var(--sidebar-width); }
        .topbar { position: sticky; z-index: 1020; top: 0; display: flex; min-height: 72px; align-items: center; justify-content: space-between; gap: 1rem; padding: .8rem clamp(1rem, 3vw, 2.4rem); border-bottom: 1px solid #e8edf2; background: rgb(255 255 255 / 92%); backdrop-filter: blur(12px); }
        .topbar-title { color: #223b52; font-size: .95rem; font-weight: 700; }
        .topbar-context { color: var(--muted); font-size: .75rem; }
        .mobile-menu { display: none; width: 42px; height: 42px; align-items: center; justify-content: center; border: 1px solid #e2e8ee; border-radius: 11px; color: var(--navy); background: #fff; }
        .mobile-menu svg { width: 21px; height: 21px; }
        .main-content { width: min(100%, 1500px); min-height: calc(100vh - 130px); margin: 0 auto; padding: clamp(1.15rem, 3vw, 2.4rem); }
        .eyebrow { color: var(--teal); font-size: .76rem; font-weight: 700; letter-spacing: .13em; text-transform: uppercase; }
        .hero-panel { position: relative; overflow: hidden; border-radius: 1.4rem; color: #fff; background: linear-gradient(120deg, #102f4f, #087e83); }
        .hero-panel::after { position: absolute; right: -5rem; bottom: -11rem; width: 25rem; height: 25rem; border: 1px solid rgb(255 255 255 / 15%); border-radius: 50%; content: ""; }
        .hero-panel > * { position: relative; z-index: 1; }
        .surface { border: 1px solid #e4edf2; border-radius: 1rem; background: #fff; box-shadow: 0 12px 32px rgb(21 50 75 / 5%); }
        .stat-value { color: var(--navy); font-size: 2rem; font-weight: 750; line-height: 1; }
        .stat-mark { width: .55rem; height: 2.4rem; border-radius: 1rem; background: var(--teal); }
        .table > :not(caption) > * > * { padding: .9rem .8rem; }
        .badge-soft { color: #126568; background: #e3f5f2; }
        .btn-teal { border-color: var(--teal); color: #fff; background: var(--teal); }
        .btn-teal:hover, .btn-teal:focus { border-color: #06676b; color: #fff; background: #06676b; }
        .form-control, .form-select { min-height: 46px; border-color: #d6e1e9; border-radius: .7rem; }
        .form-control:focus, .form-select:focus { border-color: #64b9b6; box-shadow: 0 0 0 .2rem rgb(8 126 131 / 13%); }
        .section-title { font-weight: 700; letter-spacing: -.025em; }
        .footer { padding: .8rem clamp(1rem, 3vw, 2.4rem) 1.2rem; color: #8392a1; font-size: .72rem; }

        @media (min-width: 992px) {
            .app-sidebar { position: fixed; inset: 0 auto 0 0; visibility: visible !important; transform: none !important; }
            .app-sidebar .offcanvas-header { display: none; }
            .app-sidebar .offcanvas-body { overflow-y: auto; }
        }

        @media (max-width: 991.98px) {
            .app-main { margin-left: 0; }
            .mobile-menu { display: inline-flex; }
            .topbar { min-height: 64px; }
            .main-content { min-height: calc(100vh - 120px); }
        }

        @media (max-width: 575.98px) {
            .main-content { padding: 1.1rem 1rem 1.5rem; }
            .topbar { padding: .65rem 1rem; }
            .topbar-context { display: none; }
            .hero-panel { border-radius: 1rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $user = auth()->user();
        $userRoleLabel = $user?->role === \App\Models\User::ROLE_ADMIN
            ? 'Administrator'
            : ($user?->role === \App\Models\User::ROLE_KARYAWAN ? 'Karyawan' : 'Pemohon');
        $userInitials = $user
            ? \Illuminate\Support\Str::of($user->name)->explode(' ')->take(2)->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->implode('')
            : null;
    @endphp

    <aside class="app-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="appSidebar" aria-label="Navigasi SI Kunjung Pemda">
        <div class="offcanvas-body">
          <div class="sidebar-inner">
            <div class="offcanvas-header sidebar-brand">
                <a class="d-flex align-items-center gap-3" href="{{ route('home') }}">
                    <span class="brand-mark" aria-hidden="true">SK</span>
                    <span>
                        <span class="brand-name d-block">SI Kunjung Pemda</span>
                        <span class="brand-caption d-block">LAYANAN KUNJUNGAN</span>
                    </span>
                </a>
                <button class="btn-close sidebar-close d-lg-none" type="button" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Tutup menu"></button>
            </div>
            <div class="sidebar-brand d-none d-lg-flex">
                <a class="d-flex align-items-center gap-3" href="{{ route('home') }}">
                    <span class="brand-mark" aria-hidden="true">SK</span>
                    <span>
                        <span class="brand-name d-block">SI Kunjung Pemda</span>
                        <span class="brand-caption d-block">LAYANAN KUNJUNGAN</span>
                    </span>
                </a>
            </div>

            @auth
                <div class="profile-card">
                    <span class="profile-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper($userInitials) }}</span>
                    <span class="overflow-hidden">
                        <span class="profile-name d-block">{{ $user->name }}</span>
                        @if ($userRoleLabel !== $user->name)
                            <span class="profile-role">{{ $userRoleLabel }}</span>
                        @endif
                    </span>
                </div>
            @endauth

            <nav class="sidebar-nav" aria-label="Navigasi utama">
                <div class="nav-caption">Menu utama</div>
                <a
                    class="sidebar-link {{ request()->routeIs('home') ? 'active' : '' }}"
                    href="{{ route('home') }}"
                    @if (request()->routeIs('home')) aria-current="page" @endif
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg>
                    <span>Beranda</span>
                </a>
                <a
                    class="sidebar-link {{ request()->routeIs('jadwal') ? 'active' : '' }}"
                    href="{{ route('jadwal') }}"
                    @if (request()->routeIs('jadwal')) aria-current="page" @endif
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18m-13 4h3m-3 3h7"/></svg>
                    <span>Jadwal layanan</span>
                </a>
                <a
                    class="sidebar-link {{ request()->routeIs('struktur') ? 'active' : '' }}"
                    href="{{ route('struktur') }}"
                    @if (request()->routeIs('struktur')) aria-current="page" @endif
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="5" r="3"/><circle cx="5" cy="19" r="3"/><circle cx="19" cy="19" r="3"/><path d="M12 8v4m-7 4v-2a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v2"/></svg>
                    <span>Struktur karyawan</span>
                </a>

                @auth
                    <div class="nav-caption mt-2">Ruang kerja</div>
                    <a
                        class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}"
                        @if (request()->routeIs('dashboard')) aria-current="page" @endif
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="5" rx="2"/><rect x="13" y="10" width="8" height="11" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/></svg>
                        <span>Dashboard</span>
                    </a>
                    @if ($user->role === \App\Models\User::ROLE_ADMIN)
                        <a
                            class="sidebar-link {{ request()->routeIs('admin.*') ? 'active' : '' }}"
                            href="{{ route('admin.index') }}"
                            @if (request()->routeIs('admin.*')) aria-current="page" @endif
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="5" rx="1"/><rect x="13" y="10" width="8" height="11" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/></svg>
                            <span>Manajemen sistem</span>
                        </a>
                    @elseif ($user->role === \App\Models\User::ROLE_KARYAWAN)
                        <a
                            class="sidebar-link {{ request()->routeIs('employee.*') ? 'active' : '' }}"
                            href="{{ route('employee.workspace') }}"
                            @if (request()->routeIs('employee.*')) aria-current="page" @endif
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 9h18m-13 5 2 2 4-4"/></svg>
                            <span>Ruang kerja</span>
                        </a>
                    @elseif ($user->role === \App\Models\User::ROLE_PEMOHON)
                        <a
                            class="sidebar-link {{ request()->routeIs('visits.*') ? 'active' : '' }}"
                            href="{{ route('visits.index') }}"
                            @if (request()->routeIs('visits.*')) aria-current="page" @endif
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3"/><path d="M16 3h5v5M10 14 21 3m-14 8v6h6"/></svg>
                            <span>Pengajuan kunjungan</span>
                        </a>
                    @endif
                    <a
                        class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                        href="{{ route('profile.edit') }}"
                        @if (request()->routeIs('profile.*')) aria-current="page" @endif
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M5 21a7 7 0 0 1 14 0"/><path d="m16 4 4 4"/></svg>
                        <span>Profil saya</span>
                    </a>
                @endauth
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-help">
                    <strong class="d-block text-white mb-1">Butuh informasi?</strong>
                    Lihat jadwal layanan dan data karyawan sebelum mengajukan kunjungan.
                </div>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="sidebar-link sidebar-logout" type="submit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                @else
                    <a class="btn btn-teal w-100 py-2" href="{{ route('login') }}">
                        Masuk ke akun
                    </a>
                @endauth
            </div>
          </div>
        </div>
    </aside>

    <div class="app-main">
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="mobile-menu" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Buka menu navigasi">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <div class="topbar-title">@yield('page-title', 'Layanan kunjungan')</div>
                    <div class="topbar-context">Sistem Informasi Kunjungan Pemerintah Daerah</div>
                </div>
            </div>
            @guest
                <span class="badge rounded-pill text-bg-light border d-none d-sm-inline-flex">Portal publik</span>
            @endguest
        </header>

        <main class="main-content">
            @if (session('status'))
                <div class="alert alert-success border-0 shadow-sm" role="status">{{ session('status') }}</div>
            @endif
            @yield('content')
        </main>

        <footer class="footer border-top">
            &copy; {{ now()->year }} SI Kunjung Pemda <span class="px-1">·</span> Layanan informasi kunjungan
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
