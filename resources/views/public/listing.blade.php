@extends('layouts.public')

@section('title', $title)

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <header class="border-b-2 border-slate-900 pb-4">
            <h1 class="text-2xl font-black text-slate-900 lg:text-3xl">{{ $title }}</h1>
            @if (! empty($description))
                <p class="mt-2 text-sm text-slate-600">{{ $description }}</p>
            @endif
        </header>

        @if (isset($author))
            <div class="mt-6 flex items-center gap-4 rounded-xl bg-slate-50 p-5">
                <div class="grid h-16 w-16 place-items-center rounded-full bg-red-600 text-xl font-black text-white">{{ $author->initials }}</div>
                <div>
                    <p class="font-bold text-slate-900">{{ $author->name }}</p>
                    <p class="text-sm text-slate-600">{{ $author->bio ?: 'Kontributor KabarKita' }}</p>
                </div>
            </div>
        @endif

        <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_18rem]">
            <div>
                @if ($articles->isEmpty())
                    <p class="rounded-lg bg-slate-50 p-6 text-center text-sm text-slate-500">Tidak ada artikel ditemukan.</p>
                @else
                    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($articles as $article)
                            @include('public.partials.article-card', ['article' => $article])
                        @endforeach
                    </div>
                    <div class="mt-8">
                        {{ $articles->links() }}
                    </div>
                @endif
            </div>

            <aside>
                <div class="rounded-xl border border-slate-200 p-5">
                    <h2 class="mb-4 border-b-2 border-slate-900 pb-2 text-sm font-black uppercase tracking-wide">Kategori</h2>
                    <ul class="space-y-2 text-sm">
                        @foreach ($categories as $cat)
                            <li>
                                <a href="{{ route('categories.show', $cat) }}" class="flex justify-between text-slate-700 hover:text-red-600">
                                    <span>{{ $cat->name }}</span>
                                    <span class="text-slate-400">{{ $cat->articles_count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>
    </div>
@endsection
