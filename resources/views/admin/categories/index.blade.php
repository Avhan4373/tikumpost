@extends('layouts.admin')

@section('title', 'Kategori')
@section('heading', 'Kategori')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Nama</th><th class="px-5 py-3">Slug</th><th class="px-5 py-3">Artikel</th><th class="px-5 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($categories as $category)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $category->name }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $category->slug }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $category->articles_count }}</td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="rounded border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100">Edit</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Hapus kategori ini?">
                                        @csrf @method('DELETE')
                                        <button class="rounded border border-red-300 px-2 py-1 text-xs text-red-600 hover:bg-red-50">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">Belum ada kategori.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-100 px-5 py-3">{{ $categories->links() }}</div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="mb-4 font-bold text-slate-900">Kategori Baru</h2>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-3">
                @csrf
                <input type="text" name="name" required placeholder="Nama kategori" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input type="text" name="slug" placeholder="slug (opsional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <textarea name="description" rows="2" placeholder="Deskripsi (opsional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                <select name="parent_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">— Tanpa induk —</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <input type="number" name="order" value="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <button class="w-full rounded-lg bg-red-600 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Tambah</button>
            </form>
        </div>
    </div>
@endsection
