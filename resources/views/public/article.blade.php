@extends('layouts.public')

@section('title', $article->meta_title ?: $article->title)
@section('meta_description', $article->meta_description ?: $article->excerpt_text)

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-8">
        <nav class="mb-4 text-sm text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-red-600">Beranda</a>
            @if ($article->category)
                <span class="mx-1">/</span>
                <a href="{{ route('categories.show', $article->category) }}" class="hover:text-red-600">{{ $article->category->name }}</a>
            @endif
        </nav>

        @if ($article->category)
            <span class="rounded bg-red-600 px-2 py-1 text-xs font-semibold uppercase text-white">{{ $article->category->name }}</span>
        @endif
        <h1 class="mt-3 text-3xl font-black leading-tight text-slate-900 lg:text-4xl">{{ $article->title }}</h1>

        <div class="mt-4 flex flex-wrap items-center gap-3 border-b border-slate-200 pb-4 text-sm text-slate-500">
            <a href="{{ route('authors.show', $article->author) }}" class="font-semibold text-slate-700 hover:text-red-600">{{ $article->author?->name }}</a>
            <span>&middot;</span>
            <time>{{ optional($article->published_at)->format('d F Y H:i') }}</time>
            <span>&middot;</span>
            <span>{{ $article->reading_time }} menit baca</span>
            <span>&middot;</span>
            <span>{{ number_format($article->views) }}x dibaca</span>
        </div>

        @if ($article->featured_image)
            <img src="{{ Storage::disk('public')->url($article->featured_image) }}" alt="{{ $article->title }}"
                class="mt-6 w-full rounded-xl object-cover">
        @endif

        <div class="prose prose-slate mt-6 max-w-none prose-headings:font-bold prose-a:text-red-600">
            {!! $article->body !!}
        </div>

        @if ($article->tags->isNotEmpty())
            <div class="mt-8 flex flex-wrap gap-2">
                @foreach ($article->tags as $tag)
                    <a href="{{ route('tags.show', $tag) }}" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-red-600 hover:text-white">#{{ $tag->name }}</a>
                @endforeach
            </div>
        @endif

        {{-- Komentar --}}
        <section class="mt-12 border-t border-slate-200 pt-8">
            <h2 class="text-xl font-black text-slate-900">Komentar ({{ $comments->count() }})</h2>

            @auth
                <form method="POST" action="{{ route('articles.comment', $article) }}" class="mt-4">
                    @csrf
                    <textarea name="body" rows="3" required placeholder="Tulis komentar Anda..."
                        class="w-full rounded-lg border border-slate-300 p-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100">{{ old('body') }}</textarea>
                    <button type="submit" class="mt-2 rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700">Kirim Komentar</button>
                </form>
            @else
                <p class="mt-4 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">
                    <a href="{{ route('login') }}" class="font-semibold text-red-600 hover:underline">Masuk</a> untuk ikut berkomentar.
                </p>
            @endauth

            <div class="mt-8 space-y-6">
                @forelse ($comments as $comment)
                    <div class="flex gap-3">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-slate-200 text-sm font-bold text-slate-600">{{ $comment->user->initials }}</div>
                        <div class="flex-1">
                            <div class="text-sm">
                                <span class="font-semibold text-slate-900">{{ $comment->user->name }}</span>
                                <span class="ml-2 text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-700">{{ $comment->body }}</p>

                            @foreach ($comment->replies->where('status', \App\Enums\CommentStatus::Approved) as $reply)
                                <div class="mt-3 flex gap-3 border-l-2 border-slate-100 pl-4">
                                    <div class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">{{ $reply->user->initials }}</div>
                                    <div>
                                        <div class="text-sm">
                                            <span class="font-semibold text-slate-900">{{ $reply->user->name }}</span>
                                            <span class="ml-2 text-xs text-slate-400">{{ $reply->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="mt-1 text-sm text-slate-700">{{ $reply->body }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada komentar. Jadilah yang pertama!</p>
                @endforelse
            </div>
        </section>

        {{-- Artikel terkait --}}
        @if ($related->isNotEmpty())
            <section class="mt-12 border-t border-slate-200 pt-8">
                <h2 class="mb-6 text-xl font-black text-slate-900">Artikel Terkait</h2>
                <div class="grid gap-6 sm:grid-cols-3">
                    @foreach ($related as $item)
                        @include('public.partials.article-card', ['article' => $item])
                    @endforeach
                </div>
            </section>
        @endif
    </article>
@endsection
