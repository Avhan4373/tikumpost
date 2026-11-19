@extends('layouts.admin')

@php $isEdit = $article->exists; @endphp

@section('title', $isEdit ? 'Edit Artikel' : 'Artikel Baru')
@section('heading', $isEdit ? 'Edit Artikel' : 'Tulis Artikel Baru')

@section('content')
    <form method="POST" action="{{ $isEdit ? route('admin.articles.update', $article) : route('admin.articles.store') }}"
          enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-[1fr_20rem]">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div class="space-y-5 rounded-xl border border-slate-200 bg-white p-6">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Judul</label>
                <input type="text" name="title" value="{{ old('title', $article->title) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Isi Artikel</label>
                <div class="rich-editor" data-rich-editor
                    data-upload-url="{{ route('admin.media.store') }}"
                    data-upload-max-bytes="{{ $uploadMaxBytes }}"
                    data-placeholder="Tulis isi artikel di sini...">
                    <div data-rich-editor-surface class="hidden"></div>
                    {{-- Fallback tanpa JS: textarea asli tetap bisa dipakai. --}}
                    <textarea name="body" rows="16" data-rich-editor-input aria-required="true"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm focus:border-red-500 focus:outline-none">{{ old('body', $article->body) }}</textarea>
                    <p data-rich-editor-error class="mt-2 hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800"></p>
                </div>
                <p class="mt-1 text-xs text-slate-400">
                    Gunakan toolbar untuk memformat teks, menambah tautan, dan mengunggah gambar.
                    Gambar JPG/PNG/WebP yang lebih besar dari batas server ({{ \App\Support\UploadLimit::label($uploadMaxBytes) }})
                    akan diperkecil otomatis.
                </p>
            </div>

            <details class="rounded-lg border border-slate-200 p-4">
                <summary class="cursor-pointer text-sm font-semibold text-slate-700">SEO</summary>
                <div class="mt-3 space-y-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $article->meta_title) }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Meta Description</label>
                        <textarea name="meta_description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('meta_description', $article->meta_description) }}</textarea>
                    </div>
                </div>
            </details>
        </div>

        <aside class="space-y-5">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach (\App\Enums\ArticleStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $article->status?->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>

                <label class="mt-4 block text-sm font-medium text-slate-700">Jadwal Terbit</label>
                <input type="datetime-local" name="published_at"
                    value="{{ old('published_at', optional($article->published_at)->format('Y-m-d\TH:i')) }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-slate-400">Status <b>Terbit</b> selalu terbit sekarang. Isi tanggal untuk backdate atau saat memilih <b>Terjadwal</b>.</p>

                <label class="mt-4 flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_headline" value="1" @checked(old('is_headline', $article->is_headline)) class="rounded border-slate-300">
                    Tampilkan sebagai headline
                </label>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <label class="mb-1 block text-sm font-medium text-slate-700">Kategori</label>
                <select name="category_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">— Tanpa kategori —</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $article->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <label class="mb-1 block text-sm font-medium text-slate-700">Gambar Utama</label>
                @if ($article->featured_image)
                    <img src="{{ Storage::disk('public')->url($article->featured_image) }}" class="mb-2 h-32 w-full rounded-lg object-cover">
                @endif
                <input type="file" name="featured_image" accept="image/*" class="w-full text-sm">
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="mb-2 text-sm font-medium text-slate-700">Tag</p>
                <div class="max-h-48 space-y-2 overflow-y-auto">
                    @foreach ($tags as $tag)
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                @checked(in_array($tag->id, old('tags', $isEdit ? $article->tags->pluck('id')->all() : []))) class="rounded border-slate-300">
                            {{ $tag->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 rounded-lg bg-red-600 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Simpan</button>
                <a href="{{ route('admin.articles.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium hover:bg-slate-100">Batal</a>
            </div>
        </aside>
    </form>
@endsection
