<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.theme-init')
    <title>@yield('title', 'Dashboard') &middot; Admin {{ $site['site_name'] ?? 'TikumPost' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-100 text-slate-800">
    <div class="flex min-h-full">
        {{-- Latar gelap drawer sidebar (mobile) --}}
        <div data-admin-nav-backdrop class="fixed inset-0 z-40 hidden bg-slate-900/60 backdrop-blur-sm lg:hidden"></div>

        {{-- Sidebar: drawer di layar kecil, kolom tetap di desktop --}}
        <aside id="admin-sidebar" data-admin-nav
               class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] -translate-x-full flex-col bg-slate-900 text-slate-300 shadow-2xl transition-transform duration-300 ease-out lg:sticky lg:top-0 lg:h-screen lg:w-64 lg:max-w-none lg:shrink-0 lg:translate-x-0 lg:shadow-none">
            <div class="flex h-16 shrink-0 items-center gap-2 px-4 sm:px-6">
                @include('partials.logo', ['class' => 'h-8 w-auto max-w-[8rem]', 'invert' => true])
                <span class="hidden min-w-0 flex-1 truncate font-black text-white lg:block">{{ $site['site_name'] ?? 'TikumPost' }}</span>
                <button type="button" data-admin-nav-close aria-label="Tutup menu"
                        class="ml-auto grid h-9 w-9 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-800 hover:text-white lg:hidden">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 text-sm" aria-label="Navigasi admin">
                @php
                    $menu = [
                        ['admin.dashboard', 'Dashboard', 'M3 12l9-9 9 9M5 10v10h14V10'],
                        ['admin.articles.index', 'Artikel', 'M4 4h16v16H4zM8 8h8M8 12h8M8 16h5'],
                        ['admin.categories.index', 'Kategori', 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z'],
                        ['admin.tags.index', 'Tag', 'M3 7l9-4 9 4-9 4-9-4zM3 12l9 4 9-4'],
                        ['admin.reels.index', 'Tikum Reels', 'M4 4h16v16H4zM10 9l5 3-5 3z'],
                        ['admin.infographics.index', 'Infografis', 'M4 5h16v14H4zM8 11l2.5 3 2-2.5L15 15M8.5 9.5a1 1 0 110-2 1 1 0 010 2z'],
                        ['admin.menus.index', 'Menu', 'M4 6h16M4 12h16M4 18h10'],
                        ['admin.comments.index', 'Komentar', 'M4 4h16v12H8l-4 4z'],
                        ['admin.users.index', 'Pengguna', 'M12 12a4 4 0 100-8 4 4 0 000 8zM4 20a8 8 0 0116 0'],
                        ['admin.subscribers.index', 'Pelanggan', 'M4 6h16v12H4zM4 7l8 6 8-6'],
                        ['admin.media.index', 'Media', 'M4 5h16v14H4zM4 15l5-5 4 4 3-3 4 4'],
                        ['admin.theme.edit', 'Tema', 'M4 4h16v16H4zM4 10h16M10 4v16'],
                        ['admin.settings.edit', 'Pengaturan', 'M12 8a4 4 0 100 8 4 4 0 000-8zM12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1'],
                    ];
                @endphp
                @foreach ($menu as [$route, $label, $icon])
                    <a href="{{ route($route) }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 transition {{ request()->routeIs($route.'*') ? 'bg-red-600 text-white' : 'hover:bg-slate-800' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
            <div class="border-t border-slate-800 p-4 text-sm">
                <a href="{{ route('home') }}" target="_blank" class="block rounded-lg px-3 py-2 hover:bg-slate-800">&larr; Lihat Situs</a>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 flex h-16 items-center gap-2 border-b border-slate-200 bg-white px-4 sm:gap-3 sm:px-6">
                <button type="button" data-admin-nav-toggle aria-controls="admin-sidebar" aria-expanded="false" aria-label="Buka menu"
                        class="hamburger grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-100 lg:hidden">
                    <span class="hamburger-lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
                <h1 class="min-w-0 flex-1 truncate text-base font-bold text-slate-900 sm:text-lg">@yield('heading', 'Dashboard')</h1>
                <div class="flex shrink-0 items-center gap-2 text-sm sm:gap-3">
                    @include('partials.theme-toggle')
                    <span class="hidden text-slate-500 md:inline">{{ auth()->user()->name }}</span>
                    <span class="hidden rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold uppercase text-slate-600 md:inline">{{ auth()->user()->role?->label() }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg border border-slate-300 px-3 py-1.5 font-medium hover:bg-slate-100">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6">
                <x-toast />

                @yield('content')
            </main>
        </div>
    </div>

    <x-confirm-modal />
</body>
</html>
