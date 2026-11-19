@php
    $toasts = [];

    if (session('status')) {
        $toasts[] = ['success', session('status'), 5000];
    }

    if (session('error')) {
        $toasts[] = ['error', session('error'), 8000];
    }

    if ($errors->any()) {
        $toasts[] = ['error', $errors->first(), 8000];
    }
@endphp

@if (! empty($toasts))
    <div class="pointer-events-none fixed inset-x-0 bottom-0 z-[90] flex flex-col items-center gap-3 p-4 sm:items-end sm:p-6"
         aria-live="polite"
         aria-atomic="true">
        @foreach ($toasts as [$type, $message, $delay])
            <div data-toast
                 data-toast-delay="{{ $delay }}"
                 role="status"
                 class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border bg-white p-4 shadow-xl {{ $type === 'success' ? 'border-emerald-200' : 'border-red-200' }}">

                <span class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-full {{ $type === 'success' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                    @if ($type === 'success')
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    @else
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5M12 17h.01"/>
                            <circle cx="12" cy="12" r="9"/>
                        </svg>
                    @endif
                </span>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-slate-900">{{ $type === 'success' ? 'Berhasil' : 'Perhatian' }}</p>
                    <p class="mt-0.5 break-words text-sm text-slate-600">{{ $message }}</p>
                </div>

                <button type="button"
                        data-toast-close
                        class="-mr-1 -mt-1 grid h-7 w-7 shrink-0 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                    <span class="sr-only">Tutup notifikasi</span>
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>
        @endforeach
    </div>
@endif
