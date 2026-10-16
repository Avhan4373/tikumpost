@php
    $isEdit = $menu->exists;
@endphp

<div class="space-y-3">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Label</label>
        <input type="text" name="label" value="{{ old('label', $menu->label) }}" required placeholder="Nama menu"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">URL</label>
        <input type="text" name="url" value="{{ old('url', $menu->url) }}" required placeholder="/kategori/ekonomi atau https://..."
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <p class="mt-1 text-xs text-slate-400">Gunakan path internal (mis. <code>/tikumreals</code>) atau URL lengkap untuk tautan luar.</p>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Induk</label>
        <select name="parent_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">— Tanpa induk (menu utama) —</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $menu->parent_id) == $parent->id)>{{ $parent->label }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-400">Pilih induk untuk menjadikannya submenu (maksimal 2 tingkat).</p>
    </div>

    <div class="flex gap-3">
        <div class="flex-1">
            <label class="mb-1 block text-sm font-medium text-slate-700">Target</label>
            <select name="target" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="_self" @selected(old('target', $menu->target ?? '_self') === '_self')>Tab yang sama</option>
                <option value="_blank" @selected(old('target', $menu->target ?? '_self') === '_blank')>Tab baru</option>
            </select>
        </div>
        <div class="w-28">
            <label class="mb-1 block text-sm font-medium text-slate-700">Urutan</label>
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $menu->sort_order ?? $nextOrder) }}"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-600">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $menu->is_active ?? true)) class="rounded border-slate-300 text-red-600"> Tayangkan menu
    </label>
</div>
