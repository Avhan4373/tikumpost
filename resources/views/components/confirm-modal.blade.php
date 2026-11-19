@props([
    'id' => 'confirm-delete',
])

<div id="{{ $id }}"
     data-modal
     class="fixed inset-0 z-[85] items-center justify-center overflow-y-auto p-4 sm:p-6"
     aria-hidden="true">

    <div data-modal-backdrop class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    <div data-modal-panel
         role="alertdialog"
         aria-modal="true"
         aria-labelledby="{{ $id }}-title"
         aria-describedby="{{ $id }}-message"
         class="relative my-auto w-full max-w-md rounded-2xl border border-slate-200 bg-white shadow-2xl">

        <div class="flex items-start gap-4 px-5 py-5">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-red-100 text-red-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M5 7l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2"/>
                </svg>
            </span>

            <div class="min-w-0">
                <h2 id="{{ $id }}-title" class="text-base font-bold text-slate-900">Hapus item ini?</h2>
                <p id="{{ $id }}-message" class="mt-1 break-words text-sm text-slate-600">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <button type="button"
                    data-modal-close
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                Batal
            </button>
            <button type="button"
                    data-confirm-accept
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                Ya, Hapus
            </button>
        </div>
    </div>
</div>
