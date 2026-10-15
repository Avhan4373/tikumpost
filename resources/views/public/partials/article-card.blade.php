<article class="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white transition hover:shadow-lg">
    <a href="{{ route('articles.show', $article) }}" class="block aspect-[16/9] overflow-hidden bg-slate-100">
        @if ($article->featured_image)
            <img src="{{ Storage::disk('public')->url($article->featured_image) }}" alt="{{ $article->title }}"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
        @else
            <div class="grid h-full w-full place-items-center bg-gradient-to-br from-slate-200 to-slate-300 text-slate-400">
                <span class="text-3xl font-black">KabarKita</span>
            </div>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-4">
        @if ($article->category)
            <a href="{{ route('categories.show', $article->category) }}" class="mb-2 text-xs font-semibold uppercase tracking-wide text-red-600">
                {{ $article->category->name }}
            </a>
        @endif
        <h3 class="text-lg font-bold leading-snug text-slate-900">
            <a href="{{ route('articles.show', $article) }}" class="hover:text-red-600">{{ $article->title }}</a>
        </h3>
        <p class="mt-2 line-clamp-3 flex-1 text-sm text-slate-600">{{ $article->excerpt_text }}</p>
        <div class="mt-3 flex items-center gap-2 text-xs text-slate-500">
            <span class="font-medium text-slate-700">{{ $article->author?->name }}</span>
            <span>&middot;</span>
            <time>{{ optional($article->published_at)->format('d M Y') }}</time>
        </div>
    </div>
</article>
