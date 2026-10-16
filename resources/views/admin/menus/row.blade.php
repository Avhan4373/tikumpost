<div class="flex items-center gap-3 p-3">
    <span data-drag-handle title="Geser untuk mengubah urutan"
        class="cursor-grab select-none rounded-md p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 active:cursor-grabbing">
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <circle cx="7" cy="5" r="1.4" /><circle cx="13" cy="5" r="1.4" />
            <circle cx="7" cy="10" r="1.4" /><circle cx="13" cy="10" r="1.4" />
            <circle cx="7" cy="15" r="1.4" /><circle cx="13" cy="15" r="1.4" />
        </svg>
    </span>

    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-medium text-slate-800">
            {{ $menu->label }}
            @if ($menu->target === '_blank')
                <span class="text-slate-400" title="Buka di tab baru">&nearr;</span>
            @endif
        </p>
        <p class="truncate text-xs text-slate-400">{{ $menu->href }}</p>
    </div>

    @if ($menu->is_active)
        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Aktif</span>
    @else
        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Nonaktif</span>
    @endif

    <div class="flex items-center gap-2">
        <a href="{{ route('admin.menus.edit', $menu) }}" class="rounded border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100">Edit</a>
        <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}" onsubmit="return confirm('Hapus menu ini beserta submenunya?')">
            @csrf @method('DELETE')
            <button class="rounded border border-red-300 px-2 py-1 text-xs text-red-600 hover:bg-red-50">Hapus</button>
        </form>
    </div>
</div>
