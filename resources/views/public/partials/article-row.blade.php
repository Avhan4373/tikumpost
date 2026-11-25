@php($excerpt = $excerpt ?? true)
<article class="group flex gap-4 py-4" data-article-row>
    <a href="{{ route('articles.show', $article) }}" class="relative block h-24 w-32 shrink-0 overflow-hidden rounded-xl bg-slate-100 sm:h-28 sm:w-44 {{ $article->featured_image ? 'media-frame' : '' }}">
        @if ($article->featured_image)
            <x-img :path="$article->featured_image" alt="" data-fade
                sizes="(min-width: 768px) 25vw, 40vw"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
        @endif
    </a>
    <div class="min-w-0">
        @if ($article->category)
            <a href="{{ route('categories.show', $article->category) }}"
                class="inline-flex rounded bg-slate-100 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-slate-600 transition hover:bg-brand-600 hover:text-white">{{ $article->category->name }}</a>
        @endif
        <h3 class="mt-1.5 line-clamp-2 text-base font-bold leading-snug text-slate-900">
            <a href="{{ route('articles.show', $article) }}" class="transition hover:text-brand-600">{{ $article->title }}</a>
        </h3>
        @if ($excerpt)
            <p class="mt-1 hidden line-clamp-2 text-sm leading-relaxed text-slate-600 sm:block">{{ $article->excerpt_text }}</p>
        @endif
        <time class="mt-1.5 block text-xs text-slate-400" datetime="{{ optional($article->published_at)->toAtomString() }}">
            {{ optional($article->published_at)->diffForHumans() }}
        </time>
    </div>
</article>