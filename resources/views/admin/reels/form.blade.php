@extends('layouts.admin')

@php $isEdit = $reel->exists; @endphp

@section('title', $isEdit ? 'Edit Reel' : 'Reel Baru')
@section('heading', $isEdit ? 'Edit Reel' : 'Reel Baru')

@section('content')
    <form method="POST"
          action="{{ $isEdit ? route('admin.reels.update', $reel) : route('admin.reels.store') }}"
          class="max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Judul</label>
            <input type="text" name="title" value="{{ old('title', $reel->title) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">URL Instagram</label>
            <input type="url" name="url" value="{{ old('url', $reel->url) }}" required placeholder="https://www.instagram.com/reel/..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <p class="mt-1 text-xs text-slate-400">Contoh: https://www.instagram.com/reel/xxxxxxxxx/</p>
        </div>

        <div class="flex items-center gap-6">
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $reel->is_published ?? true)) class="rounded border-slate-300 text-red-600"> Tayangkan
            </label>
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <label for="sort_order">Urutan</label>
                <input id="sort_order" type="number" name="sort_order" min="0" value="{{ old('sort_order', $reel->sort_order ?? 0) }}" class="w-24 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div class="flex gap-2">
            <button class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Simpan</button>
            <a href="{{ route('admin.reels.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm hover:bg-slate-100">Batal</a>
        </div>
    </form>
@endsection
