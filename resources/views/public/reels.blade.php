@extends('layouts.public')

@section('title', 'Tikum Reels')
@section('meta_description', 'Kumpulan reels Instagram pilihan dari Tikum. Tonton previewnya langsung di sini.')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 lg:py-10">
        <header class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-fuchsia-600 via-rose-600 to-red-600 px-6 py-10 shadow-lg shadow-rose-500/20 lg:px-12 lg:py-14" data-reveal>
            <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute -bottom-16 left-1/3 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-wide text-white backdrop-blur">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.7 3.7 0 0 1-1.38-.9 3.7 3.7 0 0 1-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16Zm0 3.68a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32Zm0 10.16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.4-10.4a1.44 1.44 0 1 1-2.88 0 1.44 1.44 0 0 1 2.88 0Z"/></svg>
                        Instagram
                    </span>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-white lg:text-4xl">Tikum Reels</h1>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-white/85">
                        Kumpulan reels Instagram pilihan. Tonton previewnya langsung di sini.
                    </p>
                </div>
                <span class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-white/15 text-3xl text-white backdrop-blur">
                    <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7L8 5Z"/></svg>
                </span>
            </div>
        </header>

        @if ($reels->isEmpty())
            <p class="mt-10 rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-sm text-slate-500">
                Belum ada reel yang ditayangkan. Silakan cek kembali nanti.
            </p>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-3" data-reveal-group>
                @foreach ($reels as $reel)
                    <article class="card-lift flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm transition hover:shadow-xl hover:shadow-slate-900/5" data-reveal>
                        <blockquote class="instagram-media" data-instgrm-permalink="{{ $reel->permalink }}" data-instgrm-version="14"
                            style="background:#fff;border:0;border-radius:1rem;margin:0;max-width:100%;min-width:0;padding:0;width:100%;">
                            <a href="{{ $reel->permalink }}" target="_blank" rel="noopener"
                                class="flex aspect-[4/5] w-full flex-col items-center justify-center gap-2 rounded-2xl bg-gradient-to-br from-fuchsia-500 via-rose-500 to-red-600 text-white">
                                <svg class="h-12 w-12" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7L8 5Z"/></svg>
                                <span class="text-sm font-semibold">Tonton di Instagram</span>
                            </a>
                        </blockquote>

                        <div class="flex flex-1 flex-col px-2 pb-1 pt-3">
                            <h2 class="line-clamp-2 text-sm font-bold leading-snug text-slate-900">{{ $reel->title }}</h2>
                            <a href="{{ $reel->permalink }}" target="_blank" rel="noopener"
                                class="link-arrow mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-fuchsia-600 hover:text-fuchsia-700">
                                Lihat di Instagram
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7m0 0H9m8 0v8"/></svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $reels->links() }}
            </div>
        @endif
    </div>

    <script async src="https://www.instagram.com/embed.js"></script>
@endsection
