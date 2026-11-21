{{--
    Sidebar kanan halaman detail berita.
    Data: $popular, $categories, $tags, $infographics.
--}}
<aside class="space-y-6" data-article-sidebar data-reveal-group>
    {{-- Terpopuler --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm" data-reveal>
        <h2 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-900">
            <span class="h-4 w-1 rounded-full bg-red-600"></span> Terpopuler
        </h2>

        @if ($popular->isEmpty())
            <p class="text-sm leading-relaxed text-slate-500">Belum ada berita populer saat ini.</p>
        @else
            <ol class="space-y-3">
                @foreach ($popular as $item)
                    <li class="flex gap-3">
                        <span class="w-5 shrink-0 pt-0.5 text-right text-base font-black leading-none text-slate-300">{{ $loop->iteration }}</span>
                        <a href="{{ route('articles.show', $item) }}"
                            class="group -m-1 flex min-w-0 flex-1 gap-3 rounded-xl p-1 transition hover:bg-slate-50">
                            <span class="h-14 w-16 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                @if ($item->featured_image)
                                    <img src="{{ Storage::disk('public')->url($item->featured_image) }}" alt="{{ $item->title }}"
                                        loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @endif
                            </span>
                            <span class="min-w-0">
                                <span class="line-clamp-2 block text-sm font-semibold leading-snug text-slate-900 transition group-hover:text-red-600">{{ $item->title }}</span>
                                <span class="mt-1 flex items-center gap-2 text-xs text-slate-400">
                                    <time>{{ optional($item->published_at)->translatedFormat('d M Y') }}</time>
                                    <span class="inline-flex items-center gap-1" title="{{ number_format($item->views) }} kali dibaca">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                        {{ number_format($item->views) }}
                                    </span>
                                </span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>

    {{-- Kategori --}}
    @if ($categories->isNotEmpty())
        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm" data-reveal>
            <h2 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-900">
                <span class="h-4 w-1 rounded-full bg-red-600"></span> Kategori
            </h2>
            <ul class="space-y-1 text-sm">
                @foreach ($categories as $cat)
                    <li>
                        <a href="{{ route('categories.show', $cat) }}"
                            class="flex items-center justify-between rounded-xl px-3 py-2 font-medium text-slate-600 transition hover:bg-slate-50 hover:text-red-600">
                            <span>{{ $cat->name }}</span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-400">{{ $cat->articles_count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Topik populer --}}
    @if ($tags->isNotEmpty())
        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm" data-reveal>
            <h2 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-900">
                <span class="h-4 w-1 rounded-full bg-red-600"></span> Topik Populer
            </h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($tags as $tag)
                    <a href="{{ route('tags.show', $tag) }}"
                        class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-red-600 hover:text-white">
                        #{{ $tag->name }}
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Infografis --}}
    @if (($infographics ?? collect())->isNotEmpty())
        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm" data-reveal>
            <h2 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-900">
                <span class="h-4 w-1 rounded-full bg-red-600"></span> Infografis
            </h2>
            <div class="space-y-3">
                @foreach ($infographics as $infographic)
                    <a href="{{ Storage::disk('public')->url($infographic->image) }}" target="_blank" rel="noopener"
                        class="group block overflow-hidden rounded-xl border border-slate-200 transition hover:border-red-200 hover:shadow-sm">
                        <img src="{{ Storage::disk('public')->url($infographic->image) }}" alt="Infografis" loading="lazy"
                            class="h-auto max-h-72 w-full bg-slate-50 object-contain transition duration-500 group-hover:scale-[1.02]">
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Newsletter --}}
    <section class="overflow-hidden rounded-2xl bg-gradient-to-br from-blue-700 to-blue-900 p-6 text-white shadow-lg" data-reveal>
        <h2 class="text-lg font-bold tracking-tight">Newsletter</h2>
        <p class="mt-2 text-sm text-blue-100">Dapatkan berita terbaru langsung ke email Anda.</p>
        <form method="POST" action="{{ route('newsletter.store') }}" class="mt-4 space-y-2">
            @csrf
            <input type="email" name="email" required placeholder="Email Anda"
                class="w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2.5 text-sm text-white placeholder-blue-200 focus:border-white focus:outline-none focus:ring-2 focus:ring-white/40">
            <button type="submit" class="btn-anim w-full rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-blue-700 transition hover:bg-blue-50">Langganan Gratis</button>
        </form>
    </section>
</aside>
