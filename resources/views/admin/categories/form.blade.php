@extends('layouts.admin')

@php $isEdit = $category->exists; @endphp

@section('title', $isEdit ? 'Edit Kategori' : 'Kategori Baru')
@section('heading', $isEdit ? 'Edit Kategori' : 'Kategori Baru')

@section('content')
    <form method="POST"
          action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
          class="max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        @if ($isEdit) @method('PUT') @endif
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Deskripsi</label>
            <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('description', $category->description) }}</textarea>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Induk</label>
            <select name="parent_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">— Tanpa induk —</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Urutan</label>
            <input type="number" name="order" value="{{ old('order', $category->order ?? 0) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div class="flex gap-2">
            <button class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Simpan</button>
            <a href="{{ route('admin.categories.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm hover:bg-slate-100">Batal</a>
        </div>
    </form>
@endsection
