@extends('layouts.admin')

@section('title', 'Tag')
@section('heading', 'Tag')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Nama</th><th class="px-5 py-3">Slug</th><th class="px-5 py-3">Artikel</th><th class="px-5 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tags as $tag)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-800">#{{ $tag->name }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $tag->slug }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $tag->articles_count }}</td>
                            <td class="px-5 py-3">
                                <form method="POST" action="{{ route('admin.tags.destroy', $tag) }}" data-confirm="Hapus tag ini?" class="flex justify-end">
                                    @csrf @method('DELETE')
                                    <button class="rounded border border-red-300 px-2 py-1 text-xs text-red-600 hover:bg-red-50">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">Belum ada tag.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-100 px-5 py-3">{{ $tags->links() }}</div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="mb-4 font-bold text-slate-900">Tag Baru</h2>
            <form method="POST" action="{{ route('admin.tags.store') }}" class="space-y-3">
                @csrf
                <input type="text" name="name" required placeholder="Nama tag" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <button class="w-full rounded-lg bg-red-600 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Tambah</button>
            </form>
        </div>
    </div>
@endsection
