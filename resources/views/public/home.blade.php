@extends('layouts.public')

@section('title', 'Beranda')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        {{-- Headline --}}
        @if ($headline->isNotEmpty())
            @php($lead = $headline->first())
            <section class="grid gap-6 lg:grid-cols-3">
                <a href="{{ route('articles.show', $lead) }}" class="group relative col-span-2 overflow-hidden rounded-2xl bg-slate-900">
                    @if ($lead->featured_image)
                        <img src="{{ Storage::disk('public')->url($lead->featured_image) }}" alt="{{ $lead->title }}"
                            class="h-80 w-full object-cover opacity-80 transition group-hover:opacity-70 lg:h-[26rem]">
                    @else
                        <div class="h-80 w-full bg-gradient-to-br from-slate-700 to-slate-900 lg:h-[26rem]"></div>
                    @endif
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-6">
                        @if ($lead->category)
                            <span class="rounded bg-red-600 px-2 py-1 text-xs font-semibold uppercase text-white">{{ $lead->category->name }}</span>
                        @endif
                        <h1 class="mt-3 text-2xl font-black leading-tight text-white lg:text-4xl">{{ $lead->title }}</h1>
                        <p class="mt-2 hidden max-w-2xl text-sm text-slate-200 lg:block">{{ $lead->excerpt_text }}</p>
                    </div>
                </a>

                <div class="flex flex-col gap-4">
                    @foreach ($headline->skip(1)->take(4) as $item)
                        <a href="{{ route('articles.show', $item) }}" class="group flex gap-3 border-b border-slate-100 pb-4 last:border-0">
                            <div class="h-20 w-24 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                @if ($item->featured_image)
                                    <img src="{{ Storage::disk('public')->url($item->featured_image) }}" alt="" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div>
                                <h3 class="text-sm font-bold leading-snug text-slate-900 group-hover:text-red-600">{{ $item->title }}</h3>
                                <time class="mt-1 block text-xs text-slate-500">{{ optional($item->published_at)->format('d M Y') }}</time>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mt-12 grid gap-10 lg:grid-cols-[1fr_20rem]">
            {{-- Berita terbaru --}}
            <section>
                <div class="mb-6 flex items-center justify-between border-b-2 border-slate-900 pb-2">
                    <h2 class="text-xl font-black uppercase tracking-tight text-slate-900">Berita Terbaru</h2>
                </div>
                @if ($latest->isEmpty())
                    <p class="text-sm text-slate-500">Belum ada berita.</p>
                @else
                    <div class="grid gap-6 sm:grid-cols-2">
                        @foreach ($latest as $article)
                            @include('public.partials.article-card', ['article' => $article])
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Sidebar --}}
            <aside class="space-y-8">
                <div class="rounded-xl border border-slate-200 p-5">
                    <h2 class="mb-4 border-b-2 border-slate-900 pb-2 text-sm font-black uppercase tracking-wide">Terpopuler</h2>
                    <ol class="space-y-4">
                        @foreach ($popular as $i => $item)
                            <li class="flex gap-3">
                                <span class="text-2xl font-black text-slate-200">{{ $i + 1 }}</span>
                                <a href="{{ route('articles.show', $item) }}" class="text-sm font-semibold leading-snug text-slate-800 hover:text-red-600">{{ $item->title }}</a>
                            </li>
                        @endforeach
                    </ol>
                </div>

                <div class="rounded-xl border border-slate-200 p-5">
                    <h2 class="mb-4 border-b-2 border-slate-900 pb-2 text-sm font-black uppercase tracking-wide">Topik Populer</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($tags as $tag)
                            <a href="{{ route('tags.show', $tag) }}" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-red-600 hover:text-white">
                                #{{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection
