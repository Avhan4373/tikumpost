@extends('layouts.public')

@section('title', $title)

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 lg:py-10">
        <header class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 to-slate-800 px-6 py-8 shadow-sm lg:px-10 lg:py-10" data-reveal>
            <h1 class="text-2xl font-bold tracking-tight text-white lg:text-3xl">{{ $title }}</h1>
            @if (! empty($description))
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-300">{{ $description }}</p>
            @endif
        </header>

        @if (isset($author))
            <div class="mt-6 flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm" data-reveal>
                <div class="grid h-16 w-16 place-items-center rounded-full bg-gradient-to-br from-red-500 to-red-700 text-xl font-bold text-white">{{ $author->initials }}</div>
                <div>
                    <p class="font-bold text-slate-900">{{ $author->name }}</p>
                    <p class="text-sm text-slate-600">{{ $author->bio ?: 'Kontributor TikumPost' }}</p>
                </div>
            </div>
        @endif

        <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_18rem]">
            <div>
                @if ($articles->isEmpty())
                    <p class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">Tidak ada artikel ditemukan.</p>
                @else
                    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3" data-reveal-group>
                        @foreach ($articles as $article)
                            @include('public.partials.article-card', ['article' => $article])
                        @endforeach
                    </div>
                    <div class="mt-8">
                        {{ $articles->links() }}
                    </div>
                @endif
            </div>

            <aside class="lg:sticky lg:top-36 lg:self-start">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm" data-reveal>
                    <h2 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-900">
                        <span class="h-4 w-1 rounded-full bg-red-600"></span> Kategori
                    </h2>
                    <ul class="space-y-1 text-sm">
                        @foreach ($categories as $cat)
                            <li>
                                <a href="{{ route('categories.show', $cat) }}" class="flex items-center justify-between rounded-xl px-3 py-2 font-medium text-slate-600 transition hover:bg-slate-50 hover:text-red-600">
                                    <span>{{ $cat->name }}</span>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-400">{{ $cat->articles_count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>
    </div>
@endsection
