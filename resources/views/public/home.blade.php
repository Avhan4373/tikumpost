@extends('layouts.public')

@section('title_full', ($site['site_name'] ?? 'TikumPost').' — '.($site['site_tagline'] ?? ''))
@section('body_class', 'bg-white')

@section('content')
    <div data-home="tvri">
        {{-- Hero --}}
        @if ($headline->isNotEmpty())
            @php($lead = $headline->first())
            <section class="mx-auto max-w-7xl px-4 py-6 lg:py-8">
                <div class="grid gap-4 lg:grid-cols-3">
                    <a data-hero-lead href="{{ route('articles.show', $lead) }}"
                        class="group relative col-span-1 block overflow-hidden rounded-2xl bg-slate-900 lg:col-span-2">
                        @if ($lead->featured_image)
                            <img src="{{ Storage::disk('public')->url($lead->featured_image) }}" alt="{{ $lead->title }}"
                                class="hero-zoom media-frame h-64 w-full object-cover transition duration-700 group-hover:scale-105 lg:h-[26rem]">
                        @else
                            <div class="h-64 w-full bg-gradient-to-br from-slate-700 to-slate-900 lg:h-[26rem]"></div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-5 lg:p-7">
                            @if ($lead->category)
                                <span class="hero-rise inline-flex rounded bg-red-600 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-white">{{ $lead->category->name }}</span>
                            @endif
                            <h1 class="hero-rise mt-3 line-clamp-3 text-xl font-bold leading-tight tracking-tight text-white lg:text-3xl" style="--reveal-delay: 90ms">{{ $lead->title }}</h1>
                            <p class="hero-rise mt-2 hidden line-clamp-2 max-w-2xl text-sm leading-relaxed text-slate-200 lg:block" style="--reveal-delay: 160ms">{{ $lead->excerpt_text }}</p>
                            <div class="hero-rise mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-300" style="--reveal-delay: 230ms">
                                <span class="font-semibold text-white">{{ $lead->author?->name }}</span>
                                <span class="text-slate-500">&bull;</span>
                                <time>{{ optional($lead->published_at)->diffForHumans() }}</time>
                                <span class="text-slate-500">&bull;</span>
                                <span>{{ $lead->reading_time }} menit baca</span>
                            </div>
                        </div>
                    </a>

                    <div class="grid grid-cols-2 gap-4">
                        @foreach ($headline->skip(1)->take(4) as $item)
                            <a data-hero-tile href="{{ route('articles.show', $item) }}"
                                class="group relative block aspect-[4/3] overflow-hidden rounded-xl bg-slate-900">
                                @if ($item->featured_image)
                                    <img src="{{ Storage::disk('public')->url($item->featured_image) }}" alt="{{ $item->title }}"
                                        loading="lazy" class="media-frame h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/30 to-transparent"></div>
                                <div class="absolute inset-x-0 bottom-0 p-3">
                                    @if ($item->category)
                                        <span class="inline-flex rounded bg-red-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">{{ $item->category->name }}</span>
                                    @endif
                                    <h2 class="mt-1.5 line-clamp-2 text-sm font-bold leading-snug text-white">{{ $item->title }}</h2>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Strip kategori --}}
        @foreach ($sections as $i => $section)
            <section class="{{ $i % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} py-8" data-section>
                <div class="mx-auto max-w-7xl px-4">
                    @include('public.partials.section-heading', [
                        'title' => $section->name,
                        'subtitle' => 'Berita terbaru seputar ' . $section->name,
                        'href' => route('categories.show', $section),
                    ])

                    <div class="grid gap-x-8 md:grid-cols-2">
                        @foreach ($section->articles as $article)
                            <div class="border-b border-slate-200">
                                @include('public.partials.article-row', ['article' => $article])
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endforeach

        {{-- Berita terbaru + sidebar --}}
        <section class="mx-auto max-w-7xl px-4 py-10">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <div>
                    @include('public.partials.section-heading', [
                        'title' => 'Berita Terbaru',
                        'subtitle' => 'Update berita terkini dari berbagai kategori',
                    ])

                    @if ($latest->isEmpty())
                        <p class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">Belum ada berita.</p>
                    @else
                        <div class="divide-y divide-slate-200">
                            @foreach ($latest as $article)
                                @include('public.partials.article-row', ['article' => $article])
                            @endforeach
                        </div>
                    @endif
                </div>

                <aside class="space-y-5">
                    {{-- Terpopuler --}}
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                        <h2 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-900">
                            <span class="h-4 w-1 rounded-full bg-red-600"></span> Terpopuler
                        </h2>
                        <ol class="space-y-3">
                            @foreach ($popular as $item)
                                <li class="flex items-start gap-3">
                                    <span class="w-5 shrink-0 text-center text-lg font-black leading-6 text-slate-300">{{ $loop->iteration }}</span>
                                    <a href="{{ route('articles.show', $item) }}" class="group flex min-w-0 flex-1 gap-3">
                                        <span class="h-14 w-16 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                            @if ($item->featured_image)
                                                <img src="{{ Storage::disk('public')->url($item->featured_image) }}" alt="" loading="lazy"
                                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                            @endif
                                        </span>
                                        <span class="min-w-0">
                                            <span class="line-clamp-2 block text-sm font-semibold leading-snug text-slate-800 transition group-hover:text-red-600">{{ $item->title }}</span>
                                            @if ($item->category)
                                                <span class="mt-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $item->category->name }}</span>
                                            @endif
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    </div>

                    {{-- Newsletter --}}
                    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-blue-700 to-blue-900 p-6 text-white shadow-lg">
                        <h2 class="text-lg font-bold tracking-tight">Newsletter</h2>
                        <p class="mt-2 text-sm text-blue-100">Dapatkan berita terbaru langsung ke email Anda.</p>
                        <form method="POST" action="{{ route('newsletter.store') }}" class="mt-4 space-y-2">
                            @csrf
                            <input type="email" name="email" required placeholder="Email Anda"
                                class="w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2.5 text-sm text-white placeholder-blue-200 focus:border-white focus:outline-none focus:ring-2 focus:ring-white/40">
                            <button type="submit" class="btn-anim w-full rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-blue-700 transition hover:bg-blue-50">Berlangganan</button>
                        </form>
                    </div>

                    {{-- Kategori --}}
                    @if ($categories->isNotEmpty())
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
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
                        </div>
                    @endif

                    {{-- Topik --}}
                    @if ($tags->isNotEmpty())
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                            <h2 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-900">
                                <span class="h-4 w-1 rounded-full bg-red-600"></span> Topik Populer
                            </h2>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($tags as $tag)
                                    <a href="{{ route('tags.show', $tag) }}" class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-red-600 hover:text-white">
                                        #{{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Infografis --}}
                    @if (($infographics ?? collect())->isNotEmpty())
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                            <h2 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-900">
                                <span class="h-4 w-1 rounded-full bg-red-600"></span> Infografis
                            </h2>
                            <div class="space-y-3">
                                @foreach ($infographics as $infographic)
                                    <button type="button"
                                        data-lightbox-src="{{ Storage::disk('public')->url($infographic->image) }}"
                                        data-lightbox-alt="Infografis"
                                        class="group block w-full overflow-hidden rounded-xl border border-slate-200 transition hover:border-red-200 hover:shadow-sm">
                                        <img src="{{ Storage::disk('public')->url($infographic->image) }}" alt="Infografis" loading="lazy"
                                            class="h-auto max-h-[28rem] w-full bg-slate-50 object-contain">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </aside>
            </div>
        </section>
    </div>

    {{-- Lightbox infografis --}}
    @if (($infographics ?? collect())->isNotEmpty())
        <div data-lightbox class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/95 p-2 sm:p-4 backdrop-blur-sm">
            <button type="button" data-lightbox-close aria-label="Tutup"
                class="absolute right-3 top-3 z-10 grid h-10 w-10 place-items-center rounded-full bg-slate-900/70 text-white transition hover:bg-slate-900">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M6 18L18 6" /></svg>
            </button>
            <img data-lightbox-image src="" alt="" class="max-h-[94vh] max-w-[96vw] rounded-xl object-contain shadow-2xl">
        </div>
    @endif
@endsection