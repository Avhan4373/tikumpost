@extends('layouts.public')

@section('title', 'Beranda')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 lg:py-10">
        {{-- Headline --}}
        @if ($headline->isNotEmpty())
            @php($lead = $headline->first())
            <section class="grid gap-5 lg:grid-cols-5">
                <a href="{{ route('articles.show', $lead) }}" class="group relative col-span-1 overflow-hidden rounded-3xl bg-slate-900 lg:col-span-3">
                    @if ($lead->featured_image)
                        <img src="{{ Storage::disk('public')->url($lead->featured_image) }}" alt="{{ $lead->title }}"
                            class="hero-zoom h-80 w-full object-cover opacity-90 transition duration-700 group-hover:scale-105 lg:h-[30rem]">
                    @else
                        <div class="h-80 w-full bg-gradient-to-br from-slate-700 to-slate-900 lg:h-[30rem]"></div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/50 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 p-6 lg:p-8">
                        @if ($lead->category)
                            <span class="hero-rise inline-flex rounded-full bg-red-600 px-3 py-1 text-xs font-bold uppercase tracking-wide text-white">{{ $lead->category->name }}</span>
                        @endif
                        <h1 class="hero-rise mt-4 text-2xl font-bold leading-tight tracking-tight text-white lg:text-4xl" style="--reveal-delay: 90ms">{{ $lead->title }}</h1>
                        <p class="hero-rise mt-3 hidden max-w-2xl text-sm leading-relaxed text-slate-200 lg:block" style="--reveal-delay: 180ms">{{ $lead->excerpt_text }}</p>
                        <div class="hero-rise mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-300" style="--reveal-delay: 270ms">
                            <span class="font-semibold text-white">{{ $lead->author?->name }}</span>
                            <span class="text-slate-500">&bull;</span>
                            <time>{{ optional($lead->published_at)->format('d M Y') }}</time>
                            <span class="text-slate-500">&bull;</span>
                            <span>{{ $lead->reading_time }} menit baca</span>
                            <span class="text-slate-500">&bull;</span>
                            <span>{{ number_format($lead->views) }}x dibaca</span>
                        </div>
                    </div>
                </a>

                <div class="hero-rise rounded-3xl border border-slate-200/80 bg-white p-2 shadow-sm lg:col-span-2" style="--reveal-delay: 140ms">
                    <div class="divide-y divide-slate-100">
                        @foreach ($headline->skip(1)->take(4) as $item)
                            <a href="{{ route('articles.show', $item) }}" class="group flex gap-3 rounded-2xl p-3 transition hover:bg-slate-50">
                                <div class="h-16 w-20 shrink-0 overflow-hidden rounded-xl bg-slate-100 {{ $item->featured_image ? 'media-frame' : '' }}">
                                    @if ($item->featured_image)
                                        <img src="{{ Storage::disk('public')->url($item->featured_image) }}" alt="" loading="lazy" data-fade class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h3 class="line-clamp-2 text-sm font-semibold leading-snug text-slate-900 transition group-hover:text-red-600">{{ $item->title }}</h3>
                                    <div class="mt-1 flex items-center gap-2 text-xs text-slate-400">
                                        <time>{{ optional($item->published_at)->format('d M Y') }}</time>
                                        <span class="inline-flex items-center gap-1" title="{{ number_format($item->views) }} kali dibaca">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                            {{ number_format($item->views) }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <div class="mt-12 grid gap-10 lg:grid-cols-[1fr_20rem]">
            {{-- Berita terbaru --}}
            <section>
                <div class="mb-6 flex items-center gap-3" data-reveal>
                    <span class="h-6 w-1.5 rounded-full bg-red-600"></span>
                    <h2 class="text-xl font-bold tracking-tight text-slate-900 lg:text-2xl">Berita Terbaru</h2>
                </div>
                @if ($latest->isEmpty())
                    <p class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">Belum ada berita.</p>
                @else
                    <div class="grid gap-6 sm:grid-cols-2" data-reveal-group>
                        @foreach ($latest as $article)
                            @include('public.partials.article-card', ['article' => $article])
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Sidebar --}}
            <aside class="space-y-6" data-reveal-group>
                @if (($infographics ?? collect())->isNotEmpty())
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm" data-reveal>
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

                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm" data-reveal>
                    <h2 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-900">
                        <span class="h-4 w-1 rounded-full bg-red-600"></span> Terpopuler
                    </h2>
                    <ol class="space-y-4">
                        @foreach ($popular as $i => $item)
                            <li class="flex gap-3">
                                <span class="text-2xl font-bold leading-none text-slate-200">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <a href="{{ route('articles.show', $item) }}" class="text-sm font-semibold leading-snug text-slate-800 transition hover:text-red-600">{{ $item->title }}</a>
                            </li>
                        @endforeach
                    </ol>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm" data-reveal>
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

                <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-red-600 to-red-800 p-6 text-white shadow-lg shadow-red-600/20" data-reveal>
                    <h2 class="text-lg font-bold tracking-tight">Jangan lewatkan berita penting</h2>
                    <p class="mt-2 text-sm text-red-100">Dapatkan ringkasan berita terbaru langsung ke email Anda.</p>
                    <form method="POST" action="{{ route('newsletter.store') }}" class="mt-4 space-y-2">
                        @csrf
                        <input type="email" name="email" required placeholder="Email Anda"
                            class="w-full rounded-xl border border-white/20 bg-white/15 px-3 py-2.5 text-sm text-white placeholder-red-100/80 focus:border-white focus:outline-none focus:ring-2 focus:ring-white/40">
                        <button type="submit" class="btn-anim w-full rounded-xl bg-[#ffffff] px-4 py-2.5 text-sm font-bold text-[#b91c1c] transition hover:bg-[#fee2e2]">Langganan Gratis</button>
                    </form>
                </div>
            </aside>
        </div>
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
