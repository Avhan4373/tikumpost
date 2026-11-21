@extends('layouts.public')

@section('title', $article->meta_title ?: $article->title)
@section('meta_description', $article->meta_description ?: $article->excerpt_text)

@section('og_type', 'article')
@if ($article->featured_image)
    @section('og_image', url(Storage::disk('public')->url($article->featured_image)))
@endif
@section('body_class', 'bg-white')

@push('meta')
    <meta property="article:published_time" content="{{ $article->published_at->toAtomString() }}">
    <meta property="article:modified_time" content="{{ $article->updated_at->toAtomString() }}">
    @if ($article->category)
        <meta property="article:section" content="{{ $article->category->name }}">
    @endif
@endpush

@push('schema')
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => $article->title,
        'description' => $article->meta_description ?: $article->excerpt_text,
        'image' => [$article->featured_image ? url(Storage::disk('public')->url($article->featured_image)) : url('favicon.ico')],
        'datePublished' => $article->published_at->toAtomString(),
        'dateModified' => $article->updated_at->toAtomString(),
        'articleSection' => $article->category?->name,
        'author' => $article->author ? ['@type' => 'Person', 'name' => $article->author->name] : null,
        'publisher' => [
            '@type' => 'Organization',
            'name' => $site['site_name'] ?? config('app.name', 'TikumPost'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => ! empty($site['site_logo']) ? url(Storage::disk('public')->url($site['site_logo'])) : url('favicon.ico'),
            ],
        ],
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('articles.show', $article)],
    ]), JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $article->title, 'item' => route('articles.show', $article)],
        ],
    ], JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
    {{-- Indikator progres baca --}}
    <div class="fixed inset-x-0 top-0 z-50 h-0.5 bg-transparent">
        <div data-reading-progress class="h-full bg-red-600 transition-[width] duration-150 ease-out" style="width: 0%"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-8 lg:py-10">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <article class="min-w-0 max-w-4xl">
            <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-slate-500" data-reveal>
                <a href="{{ route('home') }}" class="transition hover:text-red-600">Beranda</a>
                @if ($article->category)
                    <span class="text-slate-300">/</span>
                    <a href="{{ route('categories.show', $article->category) }}" class="transition hover:text-red-600">{{ $article->category->name }}</a>
                @endif
            </nav>

            @if ($article->category)
                <a href="{{ route('categories.show', $article->category) }}" data-reveal style="--reveal-delay: 40ms" class="inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-red-600">{{ $article->category->name }}</a>
            @endif

            <h1 class="mt-4 text-3xl font-bold leading-tight tracking-tight text-slate-900 lg:text-4xl" data-reveal data-protected-content style="--reveal-delay: 80ms">{{ $article->title }}</h1>

            @if ($article->excerpt)
                <p class="mt-4 text-lg leading-relaxed text-slate-600" data-reveal data-protected-content style="--reveal-delay: 140ms">{{ $article->excerpt }}</p>
            @endif

            <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-3 border-y border-slate-200 py-4" data-reveal style="--reveal-delay: 200ms">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-full bg-gradient-to-br from-slate-100 to-slate-200 text-sm font-bold text-slate-600">{{ $article->author?->initials }}</span>
                    <div class="text-sm">
                        <a href="{{ route('authors.show', $article->author) }}" class="block font-semibold text-slate-800 transition hover:text-red-600">{{ $article->author?->name }}</a>
                        <time class="text-xs text-slate-400">{{ optional($article->published_at)->translatedFormat('d F Y H:i') }}</time>
                    </div>
                </div>
                <div class="ml-auto flex items-center gap-4 text-xs text-slate-500">
                    <span>{{ $article->reading_time }} menit baca</span>
                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                    <span>{{ number_format($article->views) }}x dibaca</span>
                </div>
            </div>

            @if ($article->featured_image)
                <figure class="mt-6" data-protected-content>
                    <x-img :path="$article->featured_image" :alt="$article->title"
                        sizes="(min-width: 1024px) 768px, 100vw" loading="eager" fetchpriority="high"
                        class="w-full rounded-2xl object-cover shadow-sm" draggable="false" />
                </figure>
            @endif

            <div class="article-body mt-8" data-reveal data-protected-content>
                {!! $article->body !!}
            </div>

            @php($shareUrl = urlencode(url()->current()))
            @php($shareText = urlencode($article->title))
            <div class="mt-10 flex flex-wrap items-center gap-3 border-t border-slate-200 pt-6" data-reveal>
                <span class="text-sm font-semibold text-slate-700">Bagikan:</span>
                <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener"
                    class="btn-anim inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-emerald-500 hover:text-white">
                    WhatsApp
                </a>
                <a href="https://twitter.com/intent/tweet?text={{ $shareText }}&url={{ $shareUrl }}" target="_blank" rel="noopener"
                    class="btn-anim inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-900 hover:text-white">
                    X / Twitter
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener"
                    class="btn-anim inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-blue-600 hover:text-white">
                    Facebook
                </a>
                <button type="button" data-copy-link="{{ url()->current() }}"
                    class="btn-anim inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-red-600 hover:text-white">
                    <span data-copy-label>Tautan</span>
                </button>
            </div>

            @if ($article->tags->isNotEmpty())
                <div class="mt-6 flex flex-wrap gap-2" data-reveal>
                    @foreach ($article->tags as $tag)
                        <a href="{{ route('tags.show', $tag) }}" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600">#{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif

            {{-- Komentar --}}
            @if ($commentsEnabled ?? true)
            <section class="mt-12 border-t border-slate-200 pt-8" data-reveal>
                <h2 class="flex items-center gap-3 text-xl font-bold tracking-tight text-slate-900">
                    Komentar <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-sm font-semibold text-slate-500">{{ $comments->count() }}</span>
                </h2>

                @include('public.partials.comment-form', ['article' => $article])

                <div class="mt-8 space-y-6">
                    @forelse ($comments as $comment)
                        <div class="flex gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-slate-100 to-slate-200 text-sm font-bold text-slate-600">{{ $comment->initials() }}</span>
                            <div class="flex-1 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
                                <div class="text-sm">
                                    <span class="font-semibold text-slate-900">{{ $comment->authorName() }}</span>
                                    <span class="ml-2 text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mt-1.5 text-sm leading-relaxed text-slate-700">{{ $comment->body }}</p>

                                @foreach ($comment->replies->where('status', \App\Enums\CommentStatus::Approved) as $reply)
                                    <div class="mt-4 flex gap-3 border-l-2 border-slate-100 pl-4">
                                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">{{ $reply->initials() }}</span>
                                        <div>
                                            <div class="text-sm">
                                                <span class="font-semibold text-slate-900">{{ $reply->authorName() }}</span>
                                                <span class="ml-2 text-xs text-slate-400">{{ $reply->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="mt-1 text-sm leading-relaxed text-slate-700">{{ $reply->body }}</p>
                                        </div>
                                    </div>
                                @endforeach

                                <details class="mt-3">
                                    <summary class="cursor-pointer text-xs font-semibold text-red-600 hover:underline">Balas</summary>
                                    @include('public.partials.comment-form', ['article' => $article, 'parent' => $comment, 'compact' => true])
                                </details>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">Belum ada komentar. Jadilah yang pertama!</p>
                    @endforelse
                </div>
            </section>
            @endif
            </article>

            @include('public.partials.article-sidebar')
        </div>
    </div>

    {{-- Artikel terkait --}}
    @if ($related->isNotEmpty())
        <section class="border-t border-slate-200 bg-white py-12" data-reveal>
            <div class="mx-auto max-w-7xl px-4">
                @include('public.partials.section-heading', [
                    'title' => 'Artikel Terkait',
                    'subtitle' => 'Bacaan lain dari kategori ini',
                    'href' => $article->category ? route('categories.show', $article->category) : null,
                ])
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                    @foreach ($related as $item)
                        @include('public.partials.article-card', ['article' => $item])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
