@props([
    'id',
    'title' => null,
    'maxWidth' => 'max-w-lg',
    'autoOpen' => false,
])

<div id="{{ $id }}"
     data-modal
     @if ($autoOpen) data-modal-auto-open @endif
     class="fixed inset-0 z-[80] items-center justify-center overflow-y-auto p-4 sm:p-6"
     aria-hidden="true">

    <div data-modal-backdrop class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    <div data-modal-panel
         role="dialog"
         aria-modal="true"
         aria-labelledby="{{ $id }}-title"
         class="relative my-auto w-full {{ $maxWidth }} rounded-2xl border border-slate-200 bg-white shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
            <div class="min-w-0">
                <h2 id="{{ $id }}-title" class="truncate text-base font-bold text-slate-900">{{ $title }}</h2>
                @isset($description)
                    <p class="mt-0.5 text-xs text-slate-500">{{ $description }}</p>
                @endisset
            </div>

            <button type="button"
                    data-modal-close
                    class="-mr-1 grid h-8 w-8 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                <span class="sr-only">Tutup</span>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </div>

        <div class="px-5 py-5">
            {{ $slot }}
        </div>
    </div>
</div>
