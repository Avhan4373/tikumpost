<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') &middot; Admin KabarKita</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-100 text-slate-800">
    <div class="flex min-h-full">
        {{-- Sidebar --}}
        <aside class="hidden w-64 shrink-0 flex-col bg-slate-900 text-slate-300 lg:flex">
            <div class="flex h-16 items-center gap-2 px-6">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-red-600 font-black text-white">K</span>
                <span class="font-black text-white">KabarKita</span>
            </div>
            <nav class="flex-1 space-y-1 px-3 py-4 text-sm">
                @php
                    $menu = [
                        ['admin.dashboard', 'Dashboard', 'M3 12l9-9 9 9M5 10v10h14V10'],
                        ['admin.articles.index', 'Artikel', 'M4 4h16v16H4zM8 8h8M8 12h8M8 16h5'],
                        ['admin.categories.index', 'Kategori', 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z'],
                        ['admin.tags.index', 'Tag', 'M3 7l9-4 9 4-9 4-9-4zM3 12l9 4 9-4'],
                        ['admin.comments.index', 'Komentar', 'M4 4h16v12H8l-4 4z'],
                        ['admin.users.index', 'Pengguna', 'M12 12a4 4 0 100-8 4 4 0 000 8zM4 20a8 8 0 0116 0'],
                        ['admin.subscribers.index', 'Pelanggan', 'M4 6h16v12H4zM4 7l8 6 8-6'],
                        ['admin.media.index', 'Media', 'M4 5h16v14H4zM4 15l5-5 4 4 3-3 4 4'],
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
            <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-6">
                <h1 class="text-lg font-bold text-slate-900">@yield('heading', 'Dashboard')</h1>
                <div class="flex items-center gap-3 text-sm">
                    <span class="hidden text-slate-500 sm:inline">{{ auth()->user()->name }}</span>
                    <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold uppercase text-slate-600">{{ auth()->user()->role?->label() }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg border border-slate-300 px-3 py-1.5 font-medium hover:bg-slate-100">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-6">
                @if (session('status'))
                    <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <ul class="list-inside list-disc">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
