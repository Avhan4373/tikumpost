<header class="site-header sticky top-0 z-40 shadow-sm shadow-slate-900/[0.03]">
    {{-- Strip atas --}}
    <div class="site-header-collapse hidden bg-slate-900 text-slate-300 lg:block">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-1.5 text-xs">
            <p class="font-medium tracking-wide">{{ now()->translatedFormat('l, d F Y') }}</p>
            <p class="text-slate-400">{{ $site['site_tagline'] ?? 'Portal berita terkini, akurat, dan berimbang' }}</p>
        </div>
    </div>

    {{-- Bar utama (biru) --}}
    <div class="site-header-blue border-b border-blue-800 bg-blue-700">
        <div class="mx-auto max-w-7xl px-4">
            <div class="flex h-16 items-center gap-4">
                <a href="{{ route('home') }}" class="group flex shrink-0 items-center gap-2.5">
                    @if (! empty($site['site_logo']))
                        <img src="{{ Storage::disk('public')->url($site['site_logo']) }}" alt="{{ $site['site_name'] ?? 'TikumPost' }}" class="h-10 w-auto max-w-[10rem] object-contain transition duration-300 group-hover:scale-105">
                    @else
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-lg font-bold text-blue-700 shadow-lg transition duration-300 group-hover:scale-110">{{ Str::substr($site['site_name'] ?? 'K', 0, 1) }}</span>
                    @endif
                    <span class="hidden text-xl font-bold tracking-tight text-white sm:inline">{{ $site['site_name'] ?? 'TikumPost' }}</span>
                </a>

                {{-- Navigasi desktop --}}
                <div class="hidden min-w-0 flex-1 overflow-x-auto no-scrollbar lg:block">
                    <nav class="flex items-center gap-1 whitespace-nowrap">
                        @if (($navMenu ?? collect())->isEmpty())
                            <a href="{{ route('home') }}" class="nav-link px-3 py-2 text-sm font-semibold transition {{ request()->routeIs('home') ? 'is-active text-white' : 'text-blue-100 hover:text-white' }}">Beranda</a>
                            <a href="{{ route('reels.index') }}" class="nav-link px-3 py-2 text-sm font-semibold transition {{ request()->routeIs('reels.index') ? 'is-active text-white' : 'text-blue-100 hover:text-white' }}">Tikum Reals</a>
                            @foreach (($navCategories ?? collect()) as $cat)
                                <a href="{{ route('categories.show', $cat) }}" class="nav-link px-3 py-2 text-sm font-semibold transition {{ request()->routeIs('categories.show') && optional(request()->route('category'))->id === $cat->id ? 'is-active text-white' : 'text-blue-100 hover:text-white' }}">{{ $cat->name }}</a>
                            @endforeach
                        @else
                            @foreach ($navMenu as $item)
                                @php
                                    $itemActive = ($item->href !== '#' && request()->url() === $item->href)
                                        || $item->children->contains(fn ($child) => $child->href !== '#' && request()->url() === $child->href);
                                @endphp
                                @if ($item->children->isEmpty())
                                    <a href="{{ $item->href }}" @if ($item->target === '_blank') target="_blank" rel="noopener" @endif
                                        class="nav-link px-3 py-2 text-sm font-semibold transition {{ $itemActive ? 'is-active text-white' : 'text-blue-100 hover:text-white' }}">{{ $item->label }}</a>
                                @else
                                    <div class="group relative" data-menu-dropdown>
                                        <button type="button" data-menu-toggle aria-expanded="false" aria-haspopup="true"
                                            class="nav-link inline-flex items-center gap-1 px-3 py-2 text-sm font-semibold transition {{ $itemActive ? 'is-active text-white' : 'text-blue-100 hover:text-white' }}">
                                            {{ $item->label }}
                                            <svg class="h-3.5 w-3.5 transition duration-200 group-hover:rotate-180 group-data-[open]:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                                        </button>
                                        <div data-menu-panel
                                            class="invisible absolute left-0 top-full z-50 w-56 translate-y-1 rounded-2xl border border-slate-200 bg-white p-1.5 opacity-0 shadow-xl shadow-slate-900/10 transition duration-150 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100 group-data-[open]:visible group-data-[open]:translate-y-0 group-data-[open]:opacity-100">
                                            @foreach ($item->children as $child)
                                                <a href="{{ $child->href }}" @if ($child->target === '_blank') target="_blank" rel="noopener" @endif
                                                    class="block rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-red-600">{{ $child->label }}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </nav>
                </div>

                <div class="ml-auto flex shrink-0 items-center gap-2">
                    {{-- Pencarian desktop (tanpa JS) --}}
                    <details class="relative hidden lg:block">
                        <summary class="grid h-10 w-10 cursor-pointer list-none place-items-center rounded-xl text-white transition hover:bg-white/15 [&::-webkit-details-marker]:hidden" aria-label="Cari">
                            <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.34-4.34M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z" />
                            </svg>
                        </summary>
                        <form action="{{ route('search') }}" method="GET" class="absolute right-0 top-full z-50 mt-2 w-64 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari berita..."
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </form>
                    </details>

                    <a href="{{ route('reels.index') }}" class="btn-anim hidden items-center gap-2 rounded-full bg-red-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-red-700 xl:inline-flex">
                        <span class="h-2 w-2 rounded-full bg-white"></span> Live
                    </a>

                    <nav class="hidden items-center gap-2 text-sm sm:flex">
                        @auth
                            @if (auth()->user()->isStaff())
                                <a href="{{ route('admin.dashboard') }}" class="btn-anim rounded-full px-3 py-2 font-medium text-blue-100 transition hover:bg-white/15 hover:text-white">Dashboard</a>
                            @endif
                            <span class="hidden text-blue-100 xl:inline">{{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn-anim rounded-full border border-white/40 px-3.5 py-2 font-medium text-white transition hover:bg-white/15">Keluar</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn-anim rounded-full bg-white px-4 py-2 font-semibold text-blue-700 transition hover:bg-blue-50">Masuk</a>
                        @endauth
                    </nav>

                    <div class="hidden lg:block [&_button]:border-white/30 [&_button]:text-white [&_button:hover]:bg-white/15">
                        @include('partials.theme-toggle')
                    </div>

                    <button type="button" data-mobile-nav-toggle aria-expanded="false" aria-label="Buka menu"
                        class="hamburger grid h-10 w-10 place-items-center rounded-xl text-white transition hover:bg-white/15 lg:hidden">
                        <span class="hamburger-lines">
                            <span></span>
                            <span></span>
                            <span></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Ticker breaking news --}}
    @if (! empty($ticker) && $ticker->isNotEmpty())
        {{-- ponytail: ticker hanya beranda; kirim $ticker dari controller lain kalau perlu di semua halaman --}}
        <div data-ticker class="overflow-hidden bg-red-600 text-white">
            <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-2">
                <span class="shrink-0 rounded bg-white/15 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide">Breaking News</span>
                <div class="relative flex-1 overflow-hidden">
                    <div class="ticker-track flex w-max items-center gap-6 whitespace-nowrap text-sm">
                        @foreach ($ticker->concat($ticker) as $item)
                            <a href="{{ route('articles.show', $item) }}" class="transition hover:underline">{{ $item->title }}</a>
                            <span class="text-white/50">&bull;</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Panel mobile --}}
    <div data-mobile-nav class="hidden border-b border-slate-200 bg-white lg:hidden">
        <div class="mx-auto max-w-7xl space-y-4 px-4 py-4">
            <form action="{{ route('search') }}" method="GET" class="relative sm:hidden">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.34-4.34M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z" />
                </svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari berita..."
                    class="w-full rounded-full border border-slate-200 bg-slate-100/70 py-2.5 pl-10 pr-4 text-sm focus:border-red-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-red-100">
            </form>

            <nav class="grid gap-1">
                @if (($navMenu ?? collect())->isEmpty())
                    <a href="{{ route('home') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('home') ? 'bg-red-50 text-red-600' : 'text-slate-700 hover:bg-slate-100' }}">Beranda</a>
                    <a href="{{ route('reels.index') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('reels.index') ? 'bg-red-50 text-red-600' : 'text-slate-700 hover:bg-slate-100' }}">Tikum Reals</a>
                    @foreach (($navCategories ?? collect()) as $cat)
                        <a href="{{ route('categories.show', $cat) }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('categories.show') && optional(request()->route('category'))->id === $cat->id ? 'bg-red-50 text-red-600' : 'text-slate-700 hover:bg-slate-100' }}">{{ $cat->name }}</a>
                    @endforeach
                @else
                    @foreach ($navMenu as $item)
                        @php
                            $itemActive = ($item->href !== '#' && request()->url() === $item->href)
                                || $item->children->contains(fn ($child) => $child->href !== '#' && request()->url() === $child->href);
                        @endphp
                        @if ($item->children->isEmpty())
                            <a href="{{ $item->href }}" @if ($item->target === '_blank') target="_blank" rel="noopener" @endif
                                class="rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ $itemActive ? 'bg-red-50 text-red-600' : 'text-slate-700 hover:bg-slate-100' }}">{{ $item->label }}</a>
                        @else
                            <div class="group" data-menu-dropdown>
                                <button type="button" data-menu-toggle aria-expanded="false" aria-haspopup="true"
                                    class="flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ $itemActive ? 'bg-red-50 text-red-600' : 'text-slate-700 hover:bg-slate-100' }}">
                                    {{ $item->label }}
                                    <svg class="h-4 w-4 transition duration-200 group-data-[open]:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                                </button>
                                <div data-menu-panel class="hidden flex-col gap-1 pt-1 group-data-[open]:flex">
                                    @foreach ($item->children as $child)
                                        <a href="{{ $child->href }}" @if ($child->target === '_blank') target="_blank" rel="noopener" @endif
                                            class="rounded-xl py-2.5 pl-6 pr-3 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-red-600">{{ $child->label }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </nav>

            <div class="flex flex-col gap-2 border-t border-slate-100 pt-4">
                @auth
                    @if (auth()->user()->isStaff())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Dashboard</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">Keluar ({{ auth()->user()->name }})</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl border border-slate-300 px-3 py-2.5 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Masuk</a>
                @endauth

                @include('partials.theme-toggle', ['variant' => 'full'])

                <a href="#newsletter" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    Langganan
                </a>
            </div>
        </div>
    </div>
</header>