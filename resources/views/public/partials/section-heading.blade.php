@php($linkLabel = $linkLabel ?? 'Lihat Semua')
<div class="mb-5 flex items-end justify-between gap-4" data-section-heading>
    <div class="flex min-w-0 items-center gap-3">
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-red-600 text-white shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5M3.75 12h16.5M3.75 18.75h10.5" />
            </svg>
        </span>
        <div class="min-w-0">
            <h2 class="truncate text-lg font-bold tracking-tight text-slate-900 lg:text-xl">{{ $title }}</h2>
            @if (! empty($subtitle))
                <p class="truncate text-xs text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    @if (! empty($href))
        <a href="{{ $href }}" class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-slate-600 transition hover:text-red-600">
            {{ $linkLabel }}
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16m0 0-6-6m6 6-6 6" />
            </svg>
        </a>
    @endif
</div>