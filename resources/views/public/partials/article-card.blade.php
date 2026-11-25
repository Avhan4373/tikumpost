<article class="card-lift group flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm hover:border-slate-300 hover:shadow-xl hover:shadow-slate-900/5" data-reveal>
    <a href="{{ route('articles.show', $article) }}" class="relative block aspect-[16/10] overflow-hidden bg-slate-100 {{ $article->featured_image ? 'media-frame' : '' }}">
        @if ($article->featured_image)
            <x-img :path="$article->featured_image" :alt="$article->title" data-fade
                sizes="(min-width: 1024px) 33vw, 100vw"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
        @else
            <div class="grid h-full w-full place-items-center bg-gradient-to-br from-slate-100 to-slate-200 text-slate-300">
                <span class="text-3xl font-bold tracking-tight">TikumPost</span>
            </div>
        @endif

        @if ($article->category)
            <span class="absolute left-3 top-3 rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-brand-600 shadow-sm backdrop-blur">
                {{ $article->category->name }}
            </span>
        @endif

        <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-slate-900/70 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm backdrop-blur"
            title="{{ number_format($article->views) }} kali dibaca">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z" />
                <circle cx="12" cy="12" r="3" />
            </svg>
            {{ number_format($article->views) }}
        </span>
    </a>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="text-lg font-bold leading-snug tracking-tight text-slate-900">
            <a href="{{ route('articles.show', $article) }}" class="transition hover:text-brand-600">{{ $article->title }}</a>
        </h3>

        <p class="mt-2 line-clamp-2 flex-1 text-sm leading-relaxed text-slate-600">{{ $article->excerpt_text }}</p>

        <div class="mt-4 flex items-center gap-3 border-t border-slate-100 pt-4 text-xs text-slate-500">
            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-gradient-to-br from-slate-100 to-slate-200 font-bold text-slate-600">{{ $article->author?->initials }}</span>
            <div class="min-w-0">
                <p class="truncate font-semibold text-slate-700">{{ $article->author?->name }}</p>
                <time class="text-slate-400">{{ optional($article->published_at)->translatedFormat('d M Y') }}</time>
            </div>
            <span class="ml-auto whitespace-nowrap rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-500">{{ $article->reading_time }} mnt</span>
        </div>
    </div>
</article>
