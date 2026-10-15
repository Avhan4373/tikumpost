<header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4">
        <div class="flex h-16 items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-red-600 font-black text-white">K</span>
                <span class="text-xl font-black tracking-tight text-slate-900">Kabar<span class="text-red-600">Kita</span></span>
            </a>

            <form action="{{ route('search') }}" method="GET" class="hidden max-w-md flex-1 md:block">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari berita..."
                    class="w-full rounded-full border border-slate-300 px-4 py-2 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100">
            </form>

            <nav class="flex items-center gap-2 text-sm">
                @auth
                    @if (auth()->user()->isStaff())
                        <a href="{{ route('admin.dashboard') }}" class="hidden rounded-full px-3 py-2 font-medium text-slate-600 hover:bg-slate-100 sm:inline">Dashboard</a>
                    @endif
                    <span class="hidden text-slate-500 sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-full border border-slate-300 px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-full px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">Masuk</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-700">Daftar</a>
                @endauth
            </nav>
        </div>
    </div>

    <div class="border-t border-slate-100 bg-slate-50">
        <div class="mx-auto flex max-w-7xl gap-5 overflow-x-auto px-4 py-2 text-sm font-medium">
            <a href="{{ route('home') }}" class="whitespace-nowrap text-slate-700 hover:text-red-600">Beranda</a>
            @foreach (($navCategories ?? collect()) as $cat)
                <a href="{{ route('categories.show', $cat) }}" class="whitespace-nowrap text-slate-700 hover:text-red-600">{{ $cat->name }}</a>
            @endforeach
        </div>
    </div>
</header>
