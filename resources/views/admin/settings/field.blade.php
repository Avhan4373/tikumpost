@php
    $key = $field['key'];
    $input = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm transition focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100';
    $value = old($key, $values[$key] ?? $field['default'] ?? null);
@endphp

<div>
    @if ($field['type'] === 'boolean')
        <label for="{{ $key }}" class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-slate-50/60 px-4 py-3">
            <input type="hidden" name="{{ $key }}" value="0">
            <input type="checkbox" id="{{ $key }}" name="{{ $key }}" value="1" @checked((bool) $value)
                class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
            <span class="text-sm font-medium text-slate-700">{{ $field['label'] }}</span>
        </label>
    @else
        <label for="{{ $key }}" class="mb-1.5 block text-sm font-medium text-slate-700">{{ $field['label'] }}</label>

        @if ($field['type'] === 'select')
            <select id="{{ $key }}" name="{{ $key }}" class="{{ $input }}">
                @foreach ($field['options'] as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                @endforeach
            </select>
        @elseif ($field['type'] === 'image')
            @if ($value)
                <div class="mb-3 flex items-center gap-4 rounded-lg border border-slate-200 bg-slate-50/60 p-3">
                    <img src="{{ Storage::disk('public')->url($value) }}" alt=""
                        class="h-14 w-auto max-w-[12rem] rounded-md border border-slate-200 bg-white object-contain p-1">
                    <label class="flex items-center gap-2 text-xs font-medium text-slate-600">
                        <input type="checkbox" name="{{ $key }}_remove" value="1" class="h-4 w-4 rounded border-slate-300 text-red-600">
                        Hapus gambar
                    </label>
                </div>
            @endif
            <input type="file" id="{{ $key }}" name="{{ $key }}" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                class="block w-full cursor-pointer text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">
        @elseif ($field['type'] === 'textarea')
            <textarea id="{{ $key }}" name="{{ $key }}" rows="3" class="{{ $input }}">{{ $value }}</textarea>
        @else
            <input type="{{ $field['type'] === 'number' ? 'number' : ($field['type'] === 'email' ? 'email' : ($field['type'] === 'url' ? 'url' : 'text')) }}"
                id="{{ $key }}" name="{{ $key }}" value="{{ $value }}" class="{{ $input }}">
        @endif
    @endif

    @if (! empty($field['help']))
        <p class="mt-1.5 text-xs text-slate-400">{{ $field['help'] }}</p>
    @endif

    @error($key)
        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>
