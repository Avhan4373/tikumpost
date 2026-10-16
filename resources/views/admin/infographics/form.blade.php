@extends('layouts.admin')

@php $isEdit = $infographic->exists; @endphp

@section('title', $isEdit ? 'Edit Infografis' : 'Infografis Baru')
@section('heading', $isEdit ? 'Edit Infografis' : 'Infografis Baru')

@section('content')
    <form method="POST"
          action="{{ $isEdit ? route('admin.infographics.update', $infographic) : route('admin.infographics.store') }}"
          enctype="multipart/form-data" class="max-w-xl space-y-5">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Gambar</label>
                @if ($isEdit && $infographic->image)
                    <img src="{{ Storage::disk('public')->url($infographic->image) }}" alt=""
                        class="mb-3 max-h-64 rounded-lg border border-slate-200 bg-slate-50 object-contain">
                @endif
                <input type="file" name="image" accept="image/png,image/jpeg,image/webp" @if (! $isEdit) required @endif
                    class="block w-full cursor-pointer text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">
                <p class="mt-1 text-xs text-slate-400">
                    Format PNG/JPG/WebP, maksimal 8 MB.
                    @if ($isEdit) Biarkan kosong bila tidak ingin mengganti gambar. @endif
                </p>
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $infographic->is_published)) class="rounded border-slate-300 text-red-600">
                    Tayangkan
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    Urutan
                    <input type="number" name="sort_order" value="{{ old('sort_order', $infographic->sort_order ?? 0) }}" min="0"
                        class="w-24 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </label>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-red-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Simpan</button>
            <a href="{{ route('admin.infographics.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium hover:bg-slate-100">Batal</a>
        </div>
    </form>
@endsection
